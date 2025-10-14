<?php
/**
 * email_template.php - A modern, responsive HTML email template.
 */

/**
 * Creates a modern, responsive HTML email body.
 *
 * @param string $content The main HTML content of the email.
 * @param array $template_data Optional data to customize the template. Expected keys:
 *        'school_info' => [
 *            'name' => 'Your School Name',
 *            'logo_url' => 'URL to the school logo',
 *            'brand_color' => '#HexColor'
 *        ],
 *        'system_url' => 'The base URL of the application'
 * @return string The full HTML for the email.
 */
function create_modern_email_html($content, $template_data = []) {
    $school_name = $template_data['school_info']['name'] ?? 'Automated Report Card';
    $logo_url = $template_data['school_info']['logo_url'] ?? '';
    $brand_color = $template_data['school_info']['brand_color'] ?? '#0d6efd'; // Default Bootstrap primary blue
    $system_url = $template_data['system_url'] ?? '#';
    $current_year = date('Y');

    // Basic color calculations for shades
    $brand_color_light = adjust_color_brightness($brand_color, 20);

    $html = <<<EOD
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$school_name</title>
    <style>
        body { margin: 0; padding: 0; width: 100% !important; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; background-color: #f4f7f6; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; }
        .header { background-color: $brand_color; color: #ffffff; padding: 20px 40px; text-align: center; }
        .header img { max-width: 150px; height: auto; }
        .header h1 { margin: 10px 0 0; color: #ffffff; font-size: 24px; font-family: Arial, sans-serif; }
        .body-content { padding: 30px 40px; font-family: Arial, sans-serif; font-size: 16px; line-height: 1.6; color: #333333; }
        .body-content p { margin: 0 0 1em; }
        .button { display: inline-block; background-color: $brand_color; color: #ffffff; padding: 12px 25px; border-radius: 5px; text-decoration: none; font-weight: bold; }
        .footer { padding: 20px 40px; text-align: center; font-family: Arial, sans-serif; font-size: 12px; color: #888888; background-color: #f4f7f6;}
        .footer a { color: $brand_color; text-decoration: none; }
        .content-card { background-color: #f9f9f9; border: 1px solid #eeeeee; border-radius: 5px; padding: 20px; }
        @media screen and (max-width: 600px) {
            .container { width: 100% !important; }
            .header, .body-content, .footer { padding: 20px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; width: 100%; background-color: #f4f7f6;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 600px;">
        <tr>
            <td style="padding: 20px 0;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" class="container" style="margin: 0 auto; background-color: #ffffff; border-radius: 8px;">
                    <!-- Header -->
                    <tr>
                        <td class="header" style="background-color: $brand_color; color: #ffffff; padding: 20px 40px; text-align: center;">

EOD;

    if (!empty($logo_url)) {
        $html .= '<img src="' . htmlspecialchars($logo_url) . '" alt="' . htmlspecialchars($school_name) . ' Logo" style="max-width: 150px; height: auto;">';
    }

    $html .= <<<EOD
                            <h1 style="margin: 10px 0 0; color: #ffffff; font-size: 24px; font-family: Arial, sans-serif;">$school_name</h1>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td class="body-content" style="padding: 30px 40px; font-family: Arial, sans-serif; font-size: 16px; line-height: 1.6; color: #333333;">
                            <div class="content-card" style="background-color: #f9f9f9; border: 1px solid #eeeeee; border-radius: 5px; padding: 20px;">
                                $content
                            </div>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td class="footer" style="padding: 20px 40px; text-align: center; font-family: Arial, sans-serif; font-size: 12px; color: #888888; background-color: #f4f7f6;">
                            <p style="margin: 0 0 1em;">&copy; $current_year $school_name. All rights reserved.</p>
                            <p style="margin: 0 0 1em;">Powered by <a href="$system_url" style="color: $brand_color; text-decoration: none;">Automated Report Card System</a>.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
EOD;

    return $html;
}

/**
 * Adjusts the brightness of a hex color.
 *
 * @param string $hex The hex color code (e.g., #RRGGBB).
 * @param int $steps A value from -255 to 255. Positive to lighten, negative to darken.
 * @return string The new hex color.
 */
function adjust_color_brightness($hex, $steps) {
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));

    return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT)
         . str_pad(dechex($g), 2, '0', STR_PAD_LEFT)
         . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
}
?>