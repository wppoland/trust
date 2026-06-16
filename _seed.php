<?php
/**
 * Screenshot seed script. Run via `wp eval-file`. Not shipped.
 */

if (!defined('ABSPATH')) { exit; }

function ss_log($m) { WP_CLI::log($m); }

// --- Category ---
$cat_id = 0;
$term = term_exists('Pantry', 'product_cat');
if (!$term) { $term = wp_insert_term('Pantry', 'product_cat'); }
$cat_id = is_array($term) ? (int) $term['term_id'] : (int) $term;

// --- Helper to create a simple product ---
function ss_make_product($name, $price, $sku, $cat_id, $instock = true, $desc = '') {
    $existing = wc_get_product_id_by_sku($sku);
    if ($existing) { return wc_get_product($existing); }
    $p = new WC_Product_Simple();
    $p->set_name($name);
    $p->set_regular_price((string) $price);
    $p->set_price((string) $price);
    $p->set_sku($sku);
    $p->set_short_description($desc);
    $p->set_description($desc . ' Sourced and packed with care at Harbor & Co.');
    $p->set_manage_stock(true);
    if ($instock) {
        $p->set_stock_quantity(42);
        $p->set_stock_status('instock');
    } else {
        $p->set_stock_quantity(0);
        $p->set_stock_status('outofstock');
        $p->set_backorders('no');
    }
    $p->set_category_ids([$cat_id]);
    $p->set_catalog_visibility('visible');
    $id = $p->save();
    return wc_get_product($id);
}

$prods = [];
$prods['coffee'] = ss_make_product('Harbor House Coffee Blend', 18, 'HC-COFFEE', $cat_id, true, 'A smooth medium-roast house blend with notes of cocoa and toasted hazelnut.');
$prods['tea']    = ss_make_product('Coastal Breakfast Tea', 12, 'HC-TEA', $cat_id, true, 'A bright, malty loose-leaf breakfast tea.');
$prods['honey']  = ss_make_product('Wildflower Honey Jar', 14, 'HC-HONEY', $cat_id, true, 'Raw, unfiltered wildflower honey in a 12oz jar.');
$prods['oil']    = ss_make_product('Cold-Pressed Olive Oil', 24, 'HC-OIL', $cat_id, true, 'Single-estate extra virgin olive oil, cold-pressed.');
$prods['gran']   = ss_make_product('Maple Pecan Granola', 11, 'HC-GRAN', $cat_id, true, 'Crunchy oat granola with maple and toasted pecans.');
// Pre-order / out-of-stock product
$prods['roaster'] = ss_make_product('Limited Reserve Roast (2026)', 29, 'HC-RESERVE', $cat_id, false, 'Our limited single-origin reserve. Ships when the new harvest lands.');

// --- TRUST: ensure defaults explicitly set ---
update_option('trust_settings', [
    'enabled' => true,
    'heading' => 'Guaranteed safe checkout',
    'badges'  => ['secure_checkout', 'ssl_encrypted', 'money_back', 'card_payment'],
    'show_on_product' => true,
    'icon_color' => '#0f766e',
]);

// --- ANSWERS: settings + per-product FAQs on coffee ---
update_option('answers_settings', ['enabled' => true, 'tab_title' => '']);
update_post_meta($prods['coffee']->get_id(), '_answers_faqs', [
    ['question' => 'How is the coffee roasted?', 'answer' => 'We roast in small batches to a medium profile, then rest the beans for 48 hours before packing for peak flavour.'],
    ['question' => 'Is it whole bean or ground?', 'answer' => 'Every bag ships as whole bean to keep it fresh. Grind it just before brewing for the best cup.'],
    ['question' => 'How should I store it?', 'answer' => 'Keep the bag sealed in a cool, dark cupboard. Avoid the fridge — it introduces moisture.'],
    ['question' => 'Do you offer decaf?', 'answer' => 'Yes. A Swiss Water decaf of the same blend is available seasonally.'],
]);

// --- PREORDER: enable on reserve product, set button text ---
update_option('preorder_settings', ['enabled' => true, 'default_button_text' => 'Pre-order now']);
$prods['roaster']->update_meta_data('_preorder_enabled', 'yes');
$prods['roaster']->save();

// --- TIPPING: percent presets ---
update_option('tipping_settings', [
    'enabled' => true,
    'label' => 'Add a tip for the crew',
    'description' => 'Support our team — every tip is appreciated. Choose an amount or skip.',
    'type' => 'percent',
    'presets' => [5, 10, 15],
]);

// --- SURCHARGE: one configured fee ---
update_option('surcharge_settings', [
    'enabled' => true,
    'fees' => [
        ['label' => 'Insulated cold-pack', 'type' => 'fixed', 'amount' => 3.50, 'taxable' => false, 'enabled' => true],
    ],
]);

// --- MINIMUM: one rule + min order total + messages ---
update_option('minimum_settings', [
    'enabled' => true,
    'rules' => [
        ['scope' => 'global', 'target' => 0, 'min' => 2, 'max' => 12, 'step' => 1],
    ],
    'min_order_total' => 25.0,
    'msg_min_qty' => 'Please order at least {min} of "{product}".',
    'msg_max_qty' => 'You can order at most {max} of "{product}".',
    'msg_step_qty' => '"{product}" must be ordered in multiples of {step}.',
    'msg_min_total' => 'Your order total must be at least {min} (currently {total}).',
]);

// --- PROOF: enabled, bottom-left ---
update_option('proof_settings', [
    'enabled' => true,
    'position' => 'bottom-left',
    'anonymous_name_text' => 'Someone',
    'initial_delay' => 1,
    'display_time' => 60,
    'interval' => 3,
]);

// --- RAPID: all products, all columns ---
update_option('rapid_settings', [
    'enabled' => true,
    'scope' => 'all',
    'categories' => [],
    'show_image' => true,
    'show_sku' => true,
    'show_price' => true,
    'show_stock' => true,
    'per_page' => 12,
]);

// --- REORDER: button text, completed+processing ---
update_option('reorder_settings', [
    'button_text' => 'Order again',
    'statuses' => ['completed', 'processing'],
    'redirect' => 'cart',
]);

// --- Customer for orders / proof ---
$cust_id = email_exists('mara@example.com');
if (!$cust_id) {
    $cust_id = wp_insert_user([
        'user_login' => 'mara', 'user_pass' => 'password', 'user_email' => 'mara@example.com',
        'role' => 'customer', 'first_name' => 'Mara', 'last_name' => 'Whitfield',
    ]);
}
$cu = new WC_Customer($cust_id);
$cu->set_billing_first_name('Mara');
$cu->set_billing_last_name('Whitfield');
$cu->set_billing_city('Portland');
$cu->set_billing_state('OR');
$cu->set_billing_country('US');
$cu->set_billing_email('mara@example.com');
$cu->set_billing_address_1('88 Harbor Lane');
$cu->set_billing_postcode('97201');
$cu->save();

// --- Orders (for reorder list + proof popups) ---
function ss_make_order($cust_id, $items, $status, $city, $first) {
    $order = wc_create_order(['customer_id' => $cust_id]);
    foreach ($items as $pid => $qty) {
        $order->add_product(wc_get_product($pid), $qty);
    }
    $order->set_address([
        'first_name' => $first, 'last_name' => 'W.', 'city' => $city,
        'state' => 'OR', 'country' => 'US', 'email' => 'mara@example.com',
        'address_1' => '88 Harbor Lane', 'postcode' => '97201',
    ], 'billing');
    $order->calculate_totals();
    $order->set_status($status);
    $order->save();
    return $order;
}

// Avoid duplicate orders on re-run.
$existing_orders = wc_get_orders(['limit' => 1, 'customer_id' => $cust_id]);
if (empty($existing_orders)) {
    ss_make_order($cust_id, [$prods['coffee']->get_id() => 2, $prods['honey']->get_id() => 1], 'completed', 'Portland', 'Mara');
    ss_make_order($cust_id, [$prods['tea']->get_id() => 1, $prods['gran']->get_id() => 2], 'completed', 'Eugene', 'Devon');
    ss_make_order($cust_id, [$prods['oil']->get_id() => 1], 'processing', 'Salem', 'Priya');
}

// --- Pages: classic checkout/cart shortcodes, rapid order page ---
function ss_make_page($title, $slug, $content) {
    $p = get_page_by_path($slug);
    if ($p) {
        wp_update_post(['ID' => $p->ID, 'post_content' => $content]);
        return $p->ID;
    }
    return wp_insert_post([
        'post_title' => $title, 'post_name' => $slug, 'post_content' => $content,
        'post_status' => 'publish', 'post_type' => 'page',
    ]);
}
$cart_id = ss_make_page('Cart', 'cart-classic', '[woocommerce_cart]');
$co_id   = ss_make_page('Checkout', 'checkout-classic', '[woocommerce_checkout]');
update_option('woocommerce_cart_page_id', $cart_id);
update_option('woocommerce_checkout_page_id', $co_id);
ss_make_page('Quick Order', 'quick-order', '[rapid_order]');

// Allow guest checkout so checkout renders without login.
update_option('woocommerce_enable_guest_checkout', 'yes');
update_option('woocommerce_enable_checkout_login_reminder', 'no');
// Enable a payment gateway so checkout shows payment area.
$cod = get_option('woocommerce_cod_settings', []);
$cod['enabled'] = 'yes';
update_option('woocommerce_cod_settings', $cod);
update_option('woocommerce_bacs_settings', ['enabled' => 'yes', 'title' => 'Direct bank transfer']);

flush_rewrite_rules();

ss_log('SEED OK. coffee=' . $prods['coffee']->get_id() . ' reserve=' . $prods['roaster']->get_id()
    . ' cart=' . $cart_id . ' checkout=' . $co_id . ' cust=' . $cust_id);
echo "DONE\n";
