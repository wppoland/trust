<?php
if (!defined('ABSPATH')) { exit; }
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$map = [
  'HC-COFFEE'  => [[120, 84, 52], 'Coffee'],
  'HC-TEA'     => [[176, 134, 84], 'Tea'],
  'HC-HONEY'   => [[214, 158, 46], 'Honey'],
  'HC-OIL'     => [[122, 148, 74], 'Olive Oil'],
  'HC-GRAN'    => [[168, 120, 72], 'Granola'],
  'HC-RESERVE' => [[64, 72, 88], 'Reserve'],
];

foreach ($map as $sku => $info) {
  $pid = wc_get_product_id_by_sku($sku);
  if (!$pid) { continue; }
  $p = wc_get_product($pid);
  if ($p->get_image_id()) { continue; }
  list($rgb, $label) = $info;
  $w = 600; $h = 600;
  $im = imagecreatetruecolor($w, $h);
  $bg = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
  imagefilledrectangle($im, 0, 0, $w, $h, $bg);
  $panel = imagecolorallocatealpha($im, 255, 255, 255, 112);
  imagefilledrectangle($im, 70, 70, $w - 70, $h - 70, $panel);
  $txtcol = imagecolorallocate($im, 255, 255, 255);
  $fontsize = 5;
  $tw = imagefontwidth($fontsize) * strlen($label);
  imagestring($im, $fontsize, intval(($w - $tw) / 2), intval($h / 2 - 8), $label, $txtcol);
  $tmp = sys_get_temp_dir() . '/' . $sku . '.png';
  imagepng($im, $tmp);
  imagedestroy($im);
  $att = media_handle_sideload(['name' => $sku . '.png', 'tmp_name' => $tmp], $pid);
  if (!is_wp_error($att)) { set_post_thumbnail($pid, $att); }
}
echo "IMG OK\n";
