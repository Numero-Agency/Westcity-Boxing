/* West City Boxing Dashboard JavaScript - Enhanced UX */

jQuery(document).ready(function ($) {
  "use strict";

  console.log("WCB Dashboard JS initialized");

  // Configuration
  const config = {
    searchDelay: 300,
    animationSpeed: 300,
    notificationDuration: 3000,
  };

  let searchTimeout;

  // =======================
  // SEARCH FUNCTIONALITY
  // =======================

  // Enhanced student search with debounce
  function initStudentSearch() {
    $(document).on("input", ".student-search-input", function () {
      const searchTerm = $(this).val().trim();
      const $searchBtn = $("#wcb-student-search-btn");
      const $results = $("#wcb-student-search-results");

      // Clear previous timeout
      clearTimeout(searchTimeout);

      if (searchTerm.length < 2) {
        $results.hide().empty();
        return;
      }

      // Show loading state
      $results
        .show()
        .html('<div class="loading">🔍 Searching students...</div>');

      // Debounced search
      searchTimeout = setTimeout(function () {
        performSearch(searchTerm);
      }, config.searchDelay);
    });

    // Manual search button
    $(document).on("click", "#wcb-student-search-btn", function (e) {
      e.preventDefault();
      const searchTerm = $(".student-search-input").val().trim();

      if (searchTerm.length < 2) {
        showNotification(
          "Please enter at least 2 characters to search",
          "error"
        );
        return;
      }

      performSearch(searchTerm);
    });

    // Enter key search
    $(document).on("keypress", ".student-search-input", function (e) {
      if (e.which === 13) {
        e.preventDefault();
        $("#wcb-student-search-btn").click();
      }
    });
  }

  // Perform AJAX search
  function performSearch(searchTerm) {
    const $results = $("#wcb-student-search-results");

    $.ajax({
      url: wcb_ajax.ajax_url,
      type: "POST",
      dataType: "json",
      data: {
        action: "wcb_search_students",
        search_term: searchTerm,
        nonce: wcb_ajax.nonce,
      },
      beforeSend: function () {
        $results.html(
          '<div class="loading"><div class="loading-spinner"></div><p>Searching students...</p></div>'
        );
      },
      success: function (response) {
        if (response.success) {
          $results.html(response.data.html);

          // Animate results
          $results
            .find(".student-result-item")
            .hide()
            .each(function (index) {
              $(this)
                .delay(index * 100)
                .fadeIn(config.animationSpeed);
            });

          // Show count
          if (response.data.count > 0) {
            showNotification(`Found ${response.data.count} student(s)`);
          }
        } else {
          $results.html(
            '<div class="no-results">❌ ' + response.data + "</div>"
          );
        }
      },
      error: function (xhr, status, error) {
        console.error("Search error:", error);
        $results.html(
          '<div class="error">❌ Search failed. Please try again.</div>'
        );
        showNotification("Search failed. Please try again.", "error");
      },
    });
  }

  // =======================
  // PROFILE FUNCTIONALITY
  // =======================

  // Handle profile button clicks
  function initProfileViewer() {
    $(document).on("click", ".view-profile-btn", function (e) {
      e.preventDefault();

      const $btn = $(this);
      const studentId = $btn.data("student-id");
      const studentName = $btn
        .closest(".student-result-item")
        .find(".student-info strong")
        .text();

      if (!studentId) {
        showNotification("Invalid student ID", "error");
        return;
      }

      // Add loading state to button
      const originalText = $btn.text();
      $btn.addClass("loading").text("Loading...");

      // Load profile
      loadStudentProfile(studentId, studentName, function () {
        // Remove loading state
        $btn.removeClass("loading").text(originalText);

        // Smooth scroll to profile
        scrollToProfile();
      });
    });
  }

  // Load student profile via AJAX
  function loadStudentProfile(studentId, studentName, callback) {
    const $container = $("#wcb-student-profile-container");

    // Show loading state
    $container.html(`
            <div class="loading-profile">
                <div class="loading-spinner"></div>
                <h3>Loading Profile</h3>
                <p>Please wait while we load ${studentName}'s information...</p>
            </div>
        `);

    $.ajax({
      url: wcb_ajax.ajax_url,
      type: "POST",
      dataType: "json",
      data: {
        action: "wcb_load_student_profile",
        student_id: studentId,
        show_sessions: "true",
        show_memberships: "true",
        sessions_limit: "10",
        nonce: wcb_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          // Load profile content with animation
          $container.html(response.data.html);
          $container
            .find(".student-profile")
            .hide()
            .fadeIn(config.animationSpeed);

          showNotification(`Profile loaded for ${studentName}`, "success");

          // Initialize profile-specific features
          initProfileFeatures();
        } else {
          $container.html(`
                        <div class="error">
                            <h3>❌ Error Loading Profile</h3>
                            <p>${response.data}</p>
                            <button onclick="location.reload()" class="btn-primary">Retry</button>
                        </div>
                    `);
          showNotification(`Error loading ${studentName}'s profile`, "error");
        }

        if (callback) callback();
      },
      error: function (xhr, status, error) {
        console.error("Profile load error:", error);
        $container.html(`
                    <div class="error">
                        <h3>❌ Connection Error</h3>
                        <p>Failed to load student profile. Please check your connection and try again.</p>
                        <button onclick="location.reload()" class="btn-primary">Retry</button>
                    </div>
                `);
        showNotification("Failed to load profile", "error");

        if (callback) callback();
      },
    });
  }

  // Smooth scroll to profile container
  function scrollToProfile() {
    const $container = $("#wcb-student-profile-container");
    if ($container.length) {
      $("html, body").animate(
        {
          scrollTop: $container.offset().top - 50,
        },
        500
      );
    }
  }

  // Initialize profile-specific features
  function initProfileFeatures() {
    // Session filtering
    $("#session-type-filter")
      .off("change")
      .on("change", function () {
        const filterValue = $(this).val();
        const $rows = $(".sessions-table tbody tr");

        if (filterValue === "") {
          $rows.show();
        } else {
          $rows.hide().filter(`[data-session-type="${filterValue}"]`).show();
        }

        // Update count
        const visibleCount = $rows.filter(":visible").length;
        $(".sessions-header h3").text(`📋 Sessions (${visibleCount} shown)`);
      });

    // Add hover effects to session rows
    $(".sessions-table tbody tr").hover(
      function () {
        $(this).addClass("hover");
      },
      function () {
        $(this).removeClass("hover");
      }
    );
  }

  // =======================
  // DASHBOARD MANAGEMENT
  // =======================

  function clearDashboard() {
    // Clear search input
    $(".student-search-input").val("");

    // Clear search results
    $("#wcb-search-results").html("");

    // Hide profile container
    $("#wcb-student-profile-container").hide();

    // Focus search input
    $(".student-search-input").focus();

    // Show notification
    showNotification("Dashboard cleared", "info", 2000);
  }

  // =======================
  // KEYBOARD SHORTCUTS
  // =======================

  function initKeyboardShortcuts() {
    $(document).on("keydown", function (e) {
      // ESC to clear dashboard
      if (e.key === "Escape") {
        clearDashboard();
      }

      // Ctrl/Cmd + K to focus search
      if ((e.ctrlKey || e.metaKey) && e.key === "k") {
        e.preventDefault();
        $(".student-search-input").focus().select();
        showNotification("Search focused - start typing!");
      }

      // Ctrl/Cmd + R to refresh current profile
      if ((e.ctrlKey || e.metaKey) && e.key === "r") {
        const $profile = $(".student-profile");
        if ($profile.length) {
          e.preventDefault();
          // Find current student ID and reload
          const studentId = $profile.data("student-id");
          if (studentId) {
            loadStudentProfile(studentId, "Current Student");
          }
        }
      }
    });
  }

  // =======================
  // ENHANCED UX FEATURES
  // =======================

  function initEnhancements() {
    // Auto-focus search input
    setTimeout(function () {
      $(".student-search-input").focus();
    }, 500);

    // Add loading states to buttons
    $(document).on("click", ".btn-view, .btn-edit-small", function () {
      const $btn = $(this);
      $btn.addClass("loading");

      setTimeout(function () {
        $btn.removeClass("loading");
      }, 2000);
    });

    // Enhanced hover effects (tooltips would go here if needed)
    $("[title]").each(function () {
      // Skip tooltip initialization for now to avoid jQuery UI dependency issues
      // $(this).tooltip();
    });

    // Stats card animations
    $(".stat-card").each(function (index) {
      $(this).css("animation-delay", index * 100 + "ms");
    });
  }

  // =======================
  // INITIALIZATION
  // =======================

  function init() {
    console.log("Initializing WCB Dashboard...");

    initStudentSearch();
    initProfileViewer();
    initKeyboardShortcuts();
    initEnhancements();

    // Show welcome message
    setTimeout(function () {
      showNotification(
        "West City Boxing Dashboard loaded! Press Ctrl+K to search students.",
        "success"
      );
    }, 1000);

    console.log("WCB Dashboard ready! 🥊");
  }

  // Start everything
  init();

  // Make functions available globally for debugging
  window.wcbDashboard = {
    search: performSearch,
    loadProfile: loadStudentProfile,
    clear: clearDashboard,
    notify: showNotification,
  };
});
