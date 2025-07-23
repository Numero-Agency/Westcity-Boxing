<?php

namespace Breakdance\Partners\AnalyticsWP;

function noticeForCustomCodeScreen()
{
?>
    <style>
        #analyticswp-ad {
            display: none;
            background-color: #fff;
            border: 1px solid #ccd0d4;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
            max-width: 900px;
            box-sizing: border-box;
        }

        #dismiss-ad {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            font-size: 16px;
            cursor: pointer;
        }
    </style>

    <div id="analyticswp-ad">
        <h3 style="margin-top: 0;">Breakdance Recommendation: Use AnalyticsWP</h3>
        <p>Get detailed insights into your site's performance with AnalyticsWP, recommended by Breakdance. Enjoy a free version, or upgrade to the pro version with a special discount code exclusively for Breakdance users on our <a href="https://breakdance.com/portal/partner-discounts" target="_blank">partner discounts page</a>.</p>
        <a href="https://www.analyticswp.com/?utm_campaign=breakdance_admin_header_footer_code" target="_blank" class="button button-primary">Learn More</a>
        <button id="dismiss-ad">&times;</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var adElement = document.getElementById('analyticswp-ad');
            var dismissButton = document.getElementById('dismiss-ad');

            function setCookie(name, value, days) {
                var expires = "";
                if (days) {
                    var date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = "; expires=" + date.toUTCString();
                }
                document.cookie = name + "=" + (value || "") + expires + "; path=/";
            }

            function getCookie(name) {
                var nameEQ = name + "=";
                var ca = document.cookie.split(';');
                for (var i = 0; i < ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
                }
                return null;
            }

            if (!getCookie('analyticswpAdDismissed')) {
                adElement.style.display = 'block';
            }

            dismissButton.addEventListener('click', function() {
                adElement.style.display = 'none';
                setCookie('analyticswpAdDismissed', 'true', 300);
            });
        });
    </script>

<?php

}
