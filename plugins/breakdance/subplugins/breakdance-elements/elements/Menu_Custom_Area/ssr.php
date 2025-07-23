<?php
/**
 * @var array $propertiesData
 */

$link = $propertiesData['content']['content']['link'] ?? '';
$linkTag = $link ? 'a' : 'div';
$linkAttr = $link ? 'href="' . ($link['url'] ?? '') . '"' : '';

$blockId = $propertiesData['content']['content']['global_block'] ?? null;
$source = $propertiesData['content']['content']['menu_source'] ?? '';

$dropdown = $propertiesData['content']['content']['enable_dropdown'] ?? false;
$columns = $propertiesData['content']['content']['columns'] ?? [];

$showAnotherSection = $propertiesData['content']['content']['show_another_section'] ?? false;
$columns2 = $propertiesData['content']['content']['columns_2'] ?? [];

$dropdownWidth = $propertiesData['design']['dropdown']['wrapper']['width'] ?? null;
$isCustomWidth = $dropdownWidth || $source === 'global_block';
?>

<?php if ($dropdown): ?>
<div class="breakdance-dropdown <?php echo $isCustomWidth ? 'breakdance-dropdown--custom' : ''; ?>">
    <div class="breakdance-dropdown-toggle">
        <<?php echo $linkTag; ?> class="bde-custom-area-link breakdance-menu-link" <?php echo $linkAttr; ?>>
            %%CHILDREN%%
        </<?php echo $linkTag; ?>>

        <button class="breakdance-menu-link-arrow" type="button" aria-expanded="false" aria-controls="dropdown-%%ID%%" aria-label="Submenu"></button>
    </div>

    <div class="breakdance-dropdown-floater" aria-hidden="true" id="dropdown-%%ID%%">
      <div class="breakdance-dropdown-body">
            <?php
            if ($source === 'standard') {
                echo \Breakdance\Elements\Menu\renderMenuSection($columns);

                if ($showAnotherSection) {
                    echo \Breakdance\Elements\Menu\renderMenuSection($columns2, 'additional');
                }
            } elseif ($source === 'global_block') {
                echo '<div class="breakdance-dropdown-section">';

                if ($blockId) {
                    echo \Breakdance\Render\renderGlobalBlock($blockId);
                } else {
                    echo 'Please select a global block';
                }

                echo '</div>';
            }
            ?>
      </div>
    </div>
</div>
<?php else: ?>
  <<?php echo $linkTag; ?> class="bde-custom-area-link breakdance-menu-link" <?php echo $linkAttr; ?>>
    %%CHILDREN%%
  </<?php echo $linkTag; ?>>
<?php endif; ?>