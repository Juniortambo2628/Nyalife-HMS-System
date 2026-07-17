<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesignConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected string $cssPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cssPath = base_path('resources/css/nyalife-core.css');
    }

    private function readCss(): string
    {
        return file_get_contents($this->cssPath);
    }

    // =========================================================================
    // COLOR TOKEN COMPLETENESS
    // =========================================================================

    public function test_all_shades_of_pink_are_defined(): void
    {
        $css = $this->readCss();
        $shades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900];

        foreach ($shades as $shade) {
            $this->assertStringContainsString(".bg-pink-{$shade}", $css, "Missing .bg-pink-{$shade}");
            $this->assertStringContainsString(".text-pink-{$shade}", $css, "Missing .text-pink-{$shade}");
        }
    }

    public function test_all_shades_of_gray_are_defined(): void
    {
        $css = $this->readCss();
        $shades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900];

        foreach ($shades as $shade) {
            $this->assertStringContainsString(".bg-gray-{$shade}", $css, "Missing .bg-gray-{$shade}");
            $this->assertStringContainsString(".text-gray-{$shade}", $css, "Missing .text-gray-{$shade}");
        }
    }

    public function test_white_color_utilities_defined(): void
    {
        $css = $this->readCss();
        $this->assertStringContainsString('.text-white', $css);
        $this->assertStringContainsString('.bg-white', $css);
        $this->assertStringContainsString('bg-white', $css);
        $this->assertStringContainsString('.border-white', $css);
    }

    public function test_border_colors_defined(): void
    {
        $css = $this->readCss();
        $this->assertStringContainsString('.border-gray-100', $css);
        $this->assertStringContainsString('.border-gray-200', $css);
        $this->assertStringContainsString('.border-pink-100', $css);
        $this->assertStringContainsString('.border-pink-200', $css);
        $this->assertStringContainsString('.border-pink-500', $css);
    }

    // =========================================================================
    // LAYOUT UTILITIES
    // =========================================================================

    public function test_flex_utilities_defined(): void
    {
        $css = $this->readCss();
        $this->assertStringContainsString('.flex {', $css);
        $this->assertStringContainsString('.flex-1 {', $css);
        $this->assertStringContainsString('.flex-col {', $css);
        $this->assertStringContainsString('.flex-shrink-0 {', $css);
        $this->assertStringContainsString('.flex-wrap {', $css);
        $this->assertStringContainsString('.items-center {', $css);
        $this->assertStringContainsString('.items-end {', $css);
        $this->assertStringContainsString('.justify-between {', $css);
        $this->assertStringContainsString('.justify-center {', $css);
        $this->assertStringContainsString('.ml-auto {', $css);
    }

    public function test_grid_utilities_defined(): void
    {
        $css = $this->readCss();
        $this->assertStringContainsString('.grid {', $css);
        $this->assertStringContainsString('.grid-cols-2 {', $css);
        $this->assertStringContainsString('.grid-cols-3 {', $css);
    }

    // =========================================================================
    // BORDER RADIUS
    // =========================================================================

    public function test_border_radius_utilities_defined(): void
    {
        $css = $this->readCss();
        $this->assertStringContainsString('.rounded-full {', $css);
        $this->assertStringContainsString('.rounded-2xl {', $css);
        $this->assertStringContainsString('.rounded-tl-none {', $css);
        $this->assertStringContainsString('.rounded-tr-none {', $css);
    }

    // =========================================================================
    // SHADOW UTILITIES
    // =========================================================================

    public function test_shadow_utilities_defined(): void
    {
        $css = $this->readCss();
        $this->assertStringContainsString('.shadow-sm {', $css);
        $this->assertStringContainsString('.shadow {', $css);
        $this->assertStringContainsString('.shadow-md {', $css);
        $this->assertStringContainsString('.shadow-lg {', $css);
        $this->assertStringContainsString('.shadow-xl {', $css);
        $this->assertStringContainsString('.shadow-2xl {', $css);
        $this->assertStringContainsString('.shadow-inner {', $css);
    }

    // =========================================================================
    // POSITION UTILITIES
    // =========================================================================

    public function test_position_utilities_defined(): void
    {
        $css = $this->readCss();
        $this->assertStringContainsString('.relative {', $css);
        $this->assertStringContainsString('.absolute {', $css);
        $this->assertStringContainsString('.fixed {', $css);
        $this->assertStringContainsString('.sticky {', $css);
    }

    // =========================================================================
    // TRANSFORM UTILITIES
    // =========================================================================

    public function test_transform_utilities_defined(): void
    {
        $css = $this->readCss();
        $this->assertStringContainsString('.hover\\:scale-105:hover', $css);
        $this->assertStringContainsString('.active\\:scale-95:active', $css);
        $this->assertStringContainsString('.-translate-y-1\\/2', $css);
    }

    // =========================================================================
    // FOCUS UTILITIES
    // =========================================================================

    public function test_focus_utilities_defined(): void
    {
        $css = $this->readCss();
        $this->assertStringContainsString('.focus\\:ring-2:focus', $css);
        $this->assertStringContainsString('.focus\\:ring-pink-500:focus', $css);
        $this->assertStringContainsString('.focus\\:outline-none:focus', $css);
    }

    // =========================================================================
    // TYPOGRAPHY UTILITIES
    // =========================================================================

    public function test_typography_utilities_defined(): void
    {
        $css = $this->readCss();
        $this->assertStringContainsString('.text-xs {', $css);
        $this->assertStringContainsString('.text-sm {', $css);
        $this->assertStringContainsString('.text-center {', $css);
        $this->assertStringContainsString('.text-right {', $css);
        $this->assertStringContainsString('.text-left {', $css);
        $this->assertStringContainsString('.truncate {', $css);
        $this->assertStringContainsString('.whitespace-nowrap {', $css);
    }

    // =========================================================================
    // SPACING UTILITIES
    // =========================================================================

    public function test_spacing_utilities_defined(): void
    {
        $css = $this->readCss();
        $this->assertStringContainsString('.gap-1 {', $css);
        $this->assertStringContainsString('.gap-2 {', $css);
        $this->assertStringContainsString('.gap-3 {', $css);
        $this->assertStringContainsString('.gap-4 {', $css);
        $this->assertStringContainsString('.gap-6 {', $css);
        $this->assertStringContainsString('.gap-8 {', $css);
        $this->assertStringContainsString('.p-2 {', $css);
        $this->assertStringContainsString('.p-4 {', $css);
        $this->assertStringContainsString('.p-6 {', $css);
        $this->assertStringContainsString('.px-3 {', $css);
        $this->assertStringContainsString('.py-2 {', $css);
    }

    // =========================================================================
    // DISPLAY UTILITIES
    // =========================================================================

    public function test_display_utilities_defined(): void
    {
        $css = $this->readCss();
        $this->assertStringContainsString('.block {', $css);
        $this->assertStringContainsString('.inline-block {', $css);
        $this->assertStringContainsString('.hidden {', $css);
    }

    // =========================================================================
    // OPACITY UTILITIES
    // =========================================================================

    public function test_opacity_utilities_defined(): void
    {
        $css = $this->readCss();
        $this->assertStringContainsString('.opacity-0 {', $css);
        $this->assertStringContainsString('.opacity-30 {', $css);
        $this->assertStringContainsString('.opacity-50 {', $css);
        $this->assertStringContainsString('.opacity-70 {', $css);
        $this->assertStringContainsString('.opacity-100 {', $css);
    }

    // =========================================================================
    // SIZING UTILITIES
    // =========================================================================

    public function test_sizing_utilities_defined(): void
    {
        $css = $this->readCss();
        $this->assertStringContainsString('.w-full {', $css);
        $this->assertStringContainsString('.w-auto {', $css);
        $this->assertStringContainsString('.h-full {', $css);
        $this->assertStringContainsString('.min-h-\\[48px\\]', $css);
        $this->assertStringContainsString('.max-w-\\[75%\\]', $css);
    }

    // =========================================================================
    // Z-INDEX UTILITIES
    // =========================================================================

    public function test_zindex_utilities_defined(): void
    {
        $css = $this->readCss();
        $this->assertStringContainsString('.z-10 {', $css);
        $this->assertStringContainsString('.z-50 {', $css);
    }

    // =========================================================================
    // CURSOR & INTERACTION UTILITIES
    // =========================================================================

    public function test_interaction_utilities_defined(): void
    {
        $css = $this->readCss();
        $this->assertStringContainsString('.cursor-pointer {', $css);
        $this->assertStringContainsString('.resize-none {', $css);
        $this->assertStringContainsString('.select-none {', $css);
        $this->assertStringContainsString('.pointer-events-none {', $css);
        $this->assertStringContainsString('.pointer-events-auto {', $css);
    }

    // =========================================================================
    // OVERFLOW UTILITIES
    // =========================================================================

    public function test_overflow_utilities_defined(): void
    {
        $css = $this->readCss();
        $this->assertStringContainsString('.overflow-hidden {', $css);
        $this->assertStringContainsString('.overflow-y-auto {', $css);
        $this->assertStringContainsString('.overflow-x-auto {', $css);
    }

    // =========================================================================
    // CSS VARIABLES (DESIGN TOKENS)
    // =========================================================================

    public function test_css_variables_are_defined(): void
    {
        $css = $this->readCss();
        $this->assertStringContainsString('--primary-color:', $css);
        $this->assertStringContainsString('--secondary-color:', $css);
        $this->assertStringContainsString('--primary-color-light:', $css);
        $this->assertStringContainsString('--primary-color-dark:', $css);
        $this->assertStringContainsString('--secondary-color-light:', $css);
        $this->assertStringContainsString('--secondary-color-dark:', $css);
    }

    // =========================================================================
    // FRONTEND PAGES USE CONSISTENT CSS CLASSES
    // =========================================================================

    public function test_messages_page_uses_valid_css_classes(): void
    {
        $this->assertFileExists(resource_path('js/Pages/Messages/Index.jsx'));

        $jsx = file_get_contents(resource_path('js/Pages/Messages/Index.jsx'));

        $css = $this->readCss();

        preg_match_all('/className="([^"]+)"/', $jsx, $matches);
        foreach ($matches[1] as $classString) {
            $classes = explode(' ', $classString);
            foreach ($classes as $class) {
                $class = trim($class);
                if (empty($class)) continue;

                $isDynamic = str_contains($class, '{') || str_contains($class, '}') ||
                             str_contains($class, '&&') || str_contains($class, '===') ||
                             str_contains($class, '?') || str_contains($class, ':') ||
                             str_contains($class, 'null');
                if ($isDynamic) continue;

                if (str_starts_with($class, 'fa') || str_starts_with($class, 'btn') ||
                    str_starts_with($class, 'text') || str_starts_with($class, 'hover:') ||
                    str_starts_with($class, 'focus:') || str_starts_with($class, 'group')) {
                    continue;
                }

                $cssClass = '.' . preg_replace('/[^a-zA-Z0-9_\-\[\]\/]/', '\\\\$0', $class);
                $this->assertStringContainsString(
                    '.' . $class,
                    $css,
                    "CSS class '.{$class}' used in Messages/Index.jsx but not defined in nyalife-core.css"
                );
            }
        }
    }

    public function test_common_components_use_valid_css_classes(): void
    {
        $components = [
            'UnifiedToolbar',
            'UserAvatar',
            'StatCard',
            'PageHeader',
            'StatusBadge',
        ];

        $css = $this->readCss();

        foreach ($components as $component) {
            $path = resource_path("js/Components/{$component}.jsx");
            if (!file_exists($path)) continue;

            $content = file_get_contents($path);
            preg_match_all('/className="([^"]+)"/', $content, $matches);
            foreach ($matches[1] as $classString) {
                $classes = explode(' ', $classString);
                foreach ($classes as $class) {
                    $class = trim($class);
                    if (empty($class)) continue;

                    $isDynamic = str_contains($class, '{') || str_contains($class, '}');
                    if ($isDynamic) continue;

                    if (str_starts_with($class, 'fa') || str_starts_with($class, 'btn') ||
                        str_contains($class, 'transition') || str_contains($class, 'hover:')) {
                        continue;
                    }

                    $this->assertStringContainsString(
                        '.' . $class,
                        $css,
                        "CSS class '.{$class}' used in {$component}.jsx but not defined in nyalife-core.css"
                    );
                }
            }
        }
    }

    // =========================================================================
    // CSS FILE STRUCTURE INTEGRITY
    // =========================================================================

    public function test_css_has_required_sections(): void
    {
        $css = $this->readCss();

        $this->assertStringContainsString('COLORS:', $css);
        $this->assertStringContainsString('SPACING:', $css);
        $this->assertStringContainsString('BORDER RADIUS', $css);
        $this->assertStringContainsString('SHADOWS', $css);
        $this->assertStringContainsString('TYPOGRAPHY', $css);
        $this->assertStringContainsString('LAYOUT & FLEX', $css);
        $this->assertStringContainsString('SIZING', $css);
        $this->assertStringContainsString('OPACITY', $css);
        $this->assertStringContainsString('TRANSITIONS & ANIMATIONS', $css);
        $this->assertStringContainsString('BORDERS', $css);
        $this->assertStringContainsString('CURSOR & INTERACTION', $css);
    }

    public function test_css_no_duplicate_selectors(): void
    {
        $css = $this->readCss();
        preg_match_all('/^\.([a-zA-Z0-9_\-\[\]\/\\:]+)\s*\{/m', $css, $matches);

        $selectors = $matches[1];
        $unique = array_unique($selectors);

        $duplicates = array_diff_key($selectors, array_unique($selectors));
        if (!empty($duplicates)) {
            $this->fail("Duplicate CSS selectors found: " . implode(', ', array_unique($duplicates)));
        }
    }

    public function test_all_pink_shades_have_consistent_hex_format(): void
    {
        $css = $this->readCss();
        preg_match_all('/\.bg-pink-(\d+)\s*\{\s*background-color:\s*(#[0-9a-fA-F]{3,6})/', $css, $matches);

        $shades = array_combine($matches[1], $matches[2]);

        $this->assertArrayHasKey('100', $shades);
        $this->assertArrayHasKey('200', $shades);
        $this->assertArrayHasKey('500', $shades);
        $shades['600'] = $shades['600'] ?? null;
        $shades['700'] = $shades['700'] ?? null;

        foreach ($shades as $shade => $hex) {
            if ($hex === null) continue;
            $this->assertMatchesRegularExpression('/^#[0-9a-fA-F]{6}$/', $hex,
                "bg-pink-{$shade} should use 6-digit hex, got: {$hex}");
        }
    }

    // =========================================================================
    // !IMPORTANT ABUSE CHECK
    // =========================================================================

    public function test_css_important_usage_is_reasonable(): void
    {
        $css = $this->readCss();
        preg_match_all('/!important/', $css, $matches);
        $total = count($matches[0]);

        $lines = substr_count($css, "\n");

        $this->assertLessThan($lines * 0.7, $total,
            "Too many !important declarations ({$total} in {$lines} lines). Consider reducing.");
    }
}
