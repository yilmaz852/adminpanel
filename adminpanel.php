/**
 * =====================================================
 * B2B ADMIN PANEL V12.0 (ADMIN + WAREHOUSE + B2B PRO)
 * =====================================================
 */

if (!defined('ABSPATH')) {
    exit;
}

/* =====================================================
   1. INIT & URL REWRITES
===================================================== */
add_action('init', function () {
    add_rewrite_tag('%b2b_adm_page%', '([^&]+)');

    // Core Pages
    add_rewrite_rule('^b2b-login/?$', 'index.php?b2b_adm_page=login', 'top');
    add_rewrite_rule('^b2b-panel/?$', 'index.php?b2b_adm_page=dashboard', 'top');
    add_rewrite_rule('^b2b-panel/orders/?$', 'index.php?b2b_adm_page=orders', 'top');
    add_rewrite_rule('^b2b-panel/products/?$', 'index.php?b2b_adm_page=products', 'top');
    add_rewrite_rule('^b2b-panel/products/edit/?$', 'index.php?b2b_adm_page=product_edit', 'top');
    add_rewrite_rule('^b2b-panel/customers/?$', 'index.php?b2b_adm_page=customers', 'top');
    add_rewrite_rule('^b2b-panel/customers/edit/?$', 'index.php?b2b_adm_page=customer_edit', 'top');

    // B2B Pro System Pages (NEW)
    add_rewrite_rule('^b2b-panel/approvals/?$', 'index.php?b2b_adm_page=approvals', 'top');
    add_rewrite_rule('^b2b-panel/b2b-groups/?$', 'index.php?b2b_adm_page=b2b_groups', 'top');
    add_rewrite_rule('^b2b-panel/b2b-settings/?$', 'index.php?b2b_adm_page=b2b_settings', 'top');

    if (!get_option('b2b_rewrite_v12_fix')) {
        flush_rewrite_rules();
        update_option('b2b_rewrite_v12_fix', true);
    }
});

add_filter('query_vars', function ($vars) {
    $vars[] = 'b2b_adm_page';
    return $vars;
});

/* =====================================================
   2. SECURITY & LOGGING
===================================================== */
function b2b_adm_guard() {
    if (!is_user_logged_in()) { wp_redirect(home_url('/b2b-login')); exit; }
    if (!current_user_can('manage_options')) { wp_logout(); wp_die('Access Denied.'); }
}

function b2b_adm_add_log($pid, $type, $old, $new, $msg) {
    if ($old == $new) return;
    $u = wp_get_current_user();
    $logs = get_post_meta($pid, '_b2b_stock_log', true) ?: [];
    array_unshift($logs, ['date'=>current_time('mysql'), 'user'=>$u->display_name.' (Admin)', 'type'=>$type, 'old'=>$old, 'new'=>$new, 'msg'=>$msg]);
    update_post_meta($pid, '_b2b_stock_log', array_slice($logs, 0, 50));
}

/* =====================================================
   3. HELPER FUNCTIONS (B2B PRO)
===================================================== */
function b2b_get_groups() { return get_option('b2b_dynamic_groups', array()); }



/* =====================================================
   5. PAGE: B2B APPROVALS (BAŞVURULAR)
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'approvals') return;
    b2b_adm_guard();

    if(isset($_POST['approve_user'])) {
        $uid = intval($_POST['uid']);
        update_user_meta($uid, 'b2b_status', 'approved');
        if(!empty($_POST['grp'])) update_user_meta($uid, 'b2b_group_slug', sanitize_text_field($_POST['grp']));
        
        // Mail Gönder (Opsiyonel - Snippet içinde b2b_send_email fonksiyonu varsa çalışır)
        if(function_exists('b2b_send_email')) {
            $u = get_userdata($uid);
            b2b_send_email($u->user_email, 'Hesabınız Onaylandı', 'B2B hesabınız onaylanmıştır. Giriş yapabilirsiniz.');
        }
        echo '<div style="background:#d1fae5;color:#065f46;padding:15px;margin-bottom:20px;border-radius:8px">Kullanıcı onaylandı.</div>';
    }

    $users = get_users(['meta_key'=>'b2b_status','meta_value'=>'pending']);
    $groups = b2b_get_groups();

    b2b_adm_header('Bayi Başvuruları');
    ?>
    <div class="page-header"><h1 class="page-title">Onay Bekleyen Başvurular</h1></div>
    
    <div class="card">
        <table>
            <thead><tr><th>Şirket / İsim</th><th>E-Posta</th><th>Telefon</th><th>Grup Ata</th><th>İşlem</th></tr></thead>
            <tbody>
            <?php if(empty($users)): ?>
                <tr><td colspan="5" style="text-align:center;padding:30px;color:#999">Bekleyen başvuru yok.</td></tr>
            <?php else: foreach($users as $u): ?>
                <tr>
                    <td>
                        <strong><?= esc_html($u->billing_company ?: $u->display_name) ?></strong><br>
                        <small style="color:#6b7280"><?= esc_html($u->billing_city) ?></small>
                    </td>
                    <td><?= esc_html($u->user_email) ?></td>
                    <td><?= esc_html(get_user_meta($u->ID, 'billing_phone', true)) ?></td>
                    <td>
                        <form method="post" style="margin:0;display:flex;gap:10px">
                            <input type="hidden" name="uid" value="<?= $u->ID ?>">
                            <select name="grp" style="margin:0;padding:6px;width:150px">
                                <option value="">-- Standart --</option>
                                <?php foreach($groups as $k=>$v) echo "<option value='$k'>{$v['name']}</option>"; ?>
                            </select>
                            <button name="approve_user" class="secondary" style="padding:6px 12px;font-size:12px">Onayla</button>
                        </form>
                    </td>
                    <td><a href="<?= home_url('/b2b-panel/customers/edit?id='.$u->ID) ?>" class="button" style="padding:6px 10px;text-decoration:none;font-size:12px">Detay</a></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php b2b_adm_footer(); exit;
});

/* =====================================================
   6. PAGE: B2B GROUPS (GRUPLAR)
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'b2b_groups') return;
    b2b_adm_guard();

    // Save/Delete
    if(isset($_POST['save_grp'])) {
        $groups = b2b_get_groups();
        $slug = sanitize_title($_POST['name']);
        $groups[$slug] = [
            'name' => sanitize_text_field($_POST['name']),
            'discount' => floatval($_POST['discount']),
            'min_order' => floatval($_POST['min_order'])
        ];
        update_option('b2b_dynamic_groups', $groups);
    }
    if(isset($_GET['del'])) {
        $groups = b2b_get_groups();
        unset($groups[$_GET['del']]);
        update_option('b2b_dynamic_groups', $groups);
        wp_redirect(home_url('/b2b-panel/b2b-groups')); exit;
    }

    $groups = b2b_get_groups();
    b2b_adm_header('Grup Yönetimi');
    ?>
    <div class="page-header"><h1 class="page-title">Bayi Grupları & İndirimler</h1></div>

    <div class="grid-main" style="display:grid;grid-template-columns:1fr 2fr;gap:30px">
        <div class="card">
            <h3 style="margin-top:0">Yeni Grup Ekle</h3>
            <form method="post">
                <label>Grup Adı</label><input type="text" name="name" required>
                <label>İndirim Oranı (%)</label><input type="number" step="0.01" name="discount" value="0">
                <label>Min. Sipariş Tutarı</label><input type="number" name="min_order" value="0">
                <button name="save_grp" style="width:100%">Kaydet</button>
            </form>
        </div>

        <div class="card">
            <h3 style="margin-top:0">Mevcut Gruplar</h3>
            <table>
                <thead><tr><th>Grup</th><th>İndirim</th><th>Min. Sipariş</th><th>Üye Sayısı</th><th>İşlem</th></tr></thead>
                <tbody>
                <?php foreach($groups as $slug => $data): 
                    $count = count(get_users(['meta_key'=>'b2b_group_slug','meta_value'=>$slug]));
                ?>
                <tr>
                    <td><strong><?= esc_html($data['name']) ?></strong></td>
                    <td>%<?= $data['discount'] ?></td>
                    <td><?= wc_price($data['min_order']) ?></td>
                    <td><?= $count ?></td>
                    <td><a href="?b2b_adm_page=b2b_groups&del=<?= $slug ?>" onclick="return confirm('Silmek istediğine emin misin?')" style="color:red;text-decoration:none">Sil</a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php b2b_adm_footer(); exit;
});

/* =====================================================
   7. PAGE: B2B SETTINGS (AYARLAR)
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'b2b_settings') return;
    b2b_adm_guard();

    if(isset($_POST['save_settings'])) {
        update_option('b2b_hide_prices_guest', isset($_POST['hide_price']) ? 1 : 0);
        update_option('b2b_group_payment_rules', $_POST['pay_rules']);
        echo '<div style="background:#d1fae5;color:#065f46;padding:15px;margin-bottom:20px;border-radius:8px">Ayarlar kaydedildi.</div>';
    }

    $groups = b2b_get_groups();
    $gateways = WC()->payment_gateways->payment_gateways();
    $rules = get_option('b2b_group_payment_rules', []);
    $hide_price = get_option('b2b_hide_prices_guest', 0);

    b2b_adm_header('B2B Ayarları');
    ?>
    <div class="page-header"><h1 class="page-title">Genel Ayarlar</h1></div>
    
    <form method="post">
        <div class="card">
            <h3 style="margin-top:0">Gizlilik</h3>
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                <input type="checkbox" name="hide_price" value="1" <?= checked($hide_price, 1) ?> style="width:20px;height:20px">
                Giriş yapmayanlara (Misafir) fiyatları gizle ve "Giriş Yap" butonu göster.
            </label>
        </div>

        <div class="card">
            <h3 style="margin-top:0">Ödeme Yöntemi Kısıtlamaları</h3>
            <p style="color:#666;font-size:13px;margin-bottom:20px">Hangi grubun hangi ödeme yöntemini görebileceğini seçin.</p>
            
            <table>
                <thead>
                    <tr>
                        <th>Grup</th>
                        <?php foreach($gateways as $g) if($g->enabled=='yes') echo "<th>{$g->title}</th>"; ?>
                    </tr>
                </thead>
                <tbody>
                    <!-- GUEST/STANDARD ROW -->
                    <tr>
                        <td><strong>Misafir / Standart</strong></td>
                        <?php foreach($gateways as $id=>$g) if($g->enabled=='yes'): ?>
                        <td style="text-align:center"><input type="checkbox" name="pay_rules[guest_standard][<?= $id ?>]" value="1" <?= checked(isset($rules['guest_standard'][$id]), true) ?> style="width:16px;height:16px"></td>
                        <?php endif; ?>
                    </tr>
                    <!-- B2B GROUPS -->
                    <?php foreach($groups as $slug=>$data): ?>
                    <tr>
                        <td><strong><?= esc_html($data['name']) ?></strong></td>
                        <?php foreach($gateways as $id=>$g) if($g->enabled=='yes'): ?>
                        <td style="text-align:center"><input type="checkbox" name="pay_rules[<?= $slug ?>][<?= $id ?>]" value="1" <?= checked(isset($rules[$slug][$id]), true) ?> style="width:16px;height:16px"></td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <button name="save_settings" style="width:100%;padding:15px;font-size:16px">Tüm Ayarları Kaydet</button>
    </form>
    <?php b2b_adm_footer(); exit;
});

// ... (Buradan sonrası, önceki V11 kodundaki Login, Dashboard, Orders, Products, Product Edit ve Customers kodlarının aynısıdır. Onları buraya eklemeyi unutmayın.) ...

/* =====================================================
   4. AJAX HANDLERS
===================================================== */
// A. Order Details (Revize: Fotoğraflar ve Loglar aynı anda görünür)
add_action('wp_ajax_b2b_adm_get_details', function() {
    if (!current_user_can('manage_options')) wp_die();
    $oid = intval($_GET['order_id']);
    $order = wc_get_order($oid);
    if (!$order) wp_send_json_error('Order not found');

    // 1. Items
    $items = [];
    foreach ($order->get_items() as $item) {
        $items[] = [
            'name' => $item->get_name(),
            'sku'  => $item->get_product() ? $item->get_product()->get_sku() : '-',
            'qty'  => $item->get_quantity()
        ];
    }
    
    // 2. Customer Note
    $notes = $order->get_customer_note() ? '<div style="background:#fffbeb;color:#b45309;padding:12px;margin-bottom:15px;border-radius:6px;border:1px solid #fcd34d"><strong><i class="fa-solid fa-note-sticky"></i> Customer Note:</strong><br>'.$order->get_customer_note().'</div>' : '';
    
    // 3. WAREHOUSE OPS DATA (POD - Fotoğraf & Teslimat)
    $photos   = get_post_meta($oid, '_delivery_photos', true);
    $del_to   = get_post_meta($oid, '_delivered_to', true);
    $del_by   = get_post_meta($oid, '_delivered_by', true);
    $del_time = get_post_meta($oid, '_delivery_time', true);
    
    $ops_html = '';
    
    // Fotoğraf veya teslimat bilgisi varsa göster
    if ($photos || $del_to) {
        $ops_html .= '<div style="margin-top:20px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;">';
        $ops_html .= '<h4 style="margin:0 0 15px 0;color:#0f172a;font-size:14px;border-bottom:1px solid #e2e8f0;padding-bottom:10px;text-transform:uppercase"><i class="fa-solid fa-camera"></i> Warehouse Delivery Proof (POD)</h4>';
        
        $ops_html .= '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:15px;margin-bottom:15px;font-size:13px;color:#334155">';
        if($del_by)   $ops_html .= '<div><strong>Staff:</strong> <br>'.esc_html($del_by).'</div>';
        if($del_to)   $ops_html .= '<div><strong>Delivered To:</strong> <br>'.esc_html($del_to).'</div>';
        if($del_time) $ops_html .= '<div><strong>Time:</strong> <br>'.esc_html($del_time).'</div>';
        $ops_html .= '</div>';

        if ($photos && is_array($photos)) {
            $ops_html .= '<div><strong>Photos:</strong><div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px">';
            foreach ($photos as $url) {
                $ops_html .= '<a href="'.esc_url($url).'" target="_blank"><img src="'.esc_url($url).'" style="width:100px;height:100px;object-fit:cover;border-radius:6px;border:1px solid #cbd5e1;box-shadow:0 2px 4px rgba(0,0,0,0.1);transition:0.2s"></a>';
            }
            $ops_html .= '</div></div>';
        }
        $ops_html .= '</div>';
    } 
    // Eğer hiçbiri yoksa boş bir mesaj gösterebiliriz veya boş bırakabiliriz
    else {
        $ops_html = '<div style="margin-top:20px;padding:15px;text-align:center;color:#94a3b8;font-style:italic;border:1px dashed #e2e8f0;border-radius:8px">No delivery info uploaded yet.</div>';
    }

    // 4. WAREHOUSE LOGS (Approval Notes) - BAĞIMSIZ BLOK
    // Artık else içinde değil, her durumda kontrol edilir.
    $wh_a = get_post_meta($oid, '_warehouse_a_notes', true);
    $wh_b = get_post_meta($oid, '_warehouse_b_notes', true);
    $logs_html = '';

    if($wh_a || $wh_b) {
        $logs_html = '<div style="background:#f1f5f9;padding:15px;margin-top:15px;font-size:12px;border-radius:6px;color:#475569;border:1px solid #e2e8f0;"><strong><i class="fa-solid fa-clock-rotate-left"></i> Warehouse Logs:</strong><br><div style="white-space:pre-wrap;margin-top:5px">'.esc_html(trim($wh_a . "\n" . $wh_b)).'</div></div>';
    }

    wp_send_json_success([
        'id' => $order->get_id(),
        'date' => $order->get_date_created()->date('d.m.Y H:i'),
        'billing' => $order->get_formatted_billing_address() ?: 'No address',
        'shipping' => $order->get_formatted_shipping_address() ?: 'No address',
        'items' => $items,
        'grand_total' => $order->get_formatted_order_total(),
        // Notlar + Teslimat Bilgisi + Loglar (Hepsi birleşti)
        'extra_html' => $notes . $ops_html . $logs_html 
    ]);
});

// B. Update Status
add_action('wp_ajax_b2b_adm_update_status', function(){
    if (!current_user_can('manage_options')) wp_die();
    $order = wc_get_order(intval($_POST['order_id']));
    if($order) {
        $order->update_status(sanitize_text_field($_POST['status']), 'Admin Panel Update');
        wp_send_json_success();
    }
    wp_send_json_error();
});

// C. Warehouse Approval Override
add_action('wp_ajax_b2b_adm_wh_update', function(){
    if (!current_user_can('manage_options')) wp_die();
    $oid = intval($_POST['order_id']);
    $wh = sanitize_text_field($_POST['warehouse']);
    $note = sanitize_textarea_field($_POST['note']);
    
    $current = get_post_meta($oid, '_'.$wh.'_approved', true);
    $new = ($current === '1') ? '0' : '1';
    update_post_meta($oid, '_'.$wh.'_approved', $new);
    
    if($note) {
        $old = get_post_meta($oid, '_'.$wh.'_notes', true);
        update_post_meta($oid, '_'.$wh.'_notes', $old . "\n" . date('d.m H:i') . ' (Admin): ' . $note);
    }
    wp_send_json_success(['new_state' => ($new === '1')]);
});

/* =====================================================
   5. UI: HEADER & CSS (REVISED MENU STRUCTURE)
===================================================== */
function b2b_adm_header($title) {
    // Bekleyen Başvuru Sayısını Hesapla
    $pending_count = count(get_users(['meta_key'=>'b2b_status','meta_value'=>'pending']));
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?> | Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root{--primary:#0f172a;--accent:#3b82f6;--bg:#f3f4f6;--white:#ffffff;--border:#e5e7eb;--text:#1f2937}
        body{margin:0;font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;font-size:14px}
        
        .sidebar{width:260px;background:var(--primary);color:#9ca3af;flex-shrink:0;position:fixed;height:100%;z-index:100;display:flex;flex-direction:column}
        .sidebar-head{padding:25px;color:var(--white);font-weight:700;font-size:1.2rem;border-bottom:1px solid rgba(255,255,255,0.1)}
        
        /* New Menu Styles */
        .sidebar-nav{padding:15px 10px;flex:1;overflow-y:auto} /* Scroll eklendi */
        .sidebar-group{padding:15px 10px 5px 10px;font-size:10px;text-transform:uppercase;color:#64748b;font-weight:700;letter-spacing:0.5px}
        
        .sidebar-nav a{display:flex;align-items:center;gap:12px;padding:12px 15px;color:inherit;text-decoration:none;border-radius:8px;margin-bottom:4px;transition:0.2s}
        .sidebar-nav a:hover, .sidebar-nav a.active{background:rgba(255,255,255,0.1);color:var(--white)}
        .sidebar-nav a.active{background:var(--accent)}
        
        .badge-count{background:#ef4444;color:#fff;font-size:10px;padding:2px 6px;border-radius:10px;margin-left:auto;font-weight:700}
        
        .main{margin-left:260px;flex:1;padding:40px;width:100%}
        
        .card{background:var(--white);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.05);padding:25px;border:1px solid var(--border);margin-bottom:25px}
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px}
        .page-title{font-size:24px;font-weight:700;color:var(--primary);margin:0}
        
        input,select,textarea{width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;box-sizing:border-box;margin-bottom:15px}
        button{background:var(--accent);color:var(--white);border:none;padding:10px 20px;border-radius:6px;cursor:pointer;font-weight:600;font-size:14px;transition:0.2s}
        button:hover{background:#2563eb}
        button.secondary{background:var(--white);border:1px solid #d1d5db;color:#374151}
        button.secondary:hover{background:#f9fafb}
        
        table{width:100%;border-collapse:collapse;font-size:13px}
        th{background:#f8fafc;padding:12px;text-align:left;font-weight:600;color:#4b5563;border-bottom:1px solid var(--border);text-transform:uppercase;font-size:11px}
        td{padding:12px;border-bottom:1px solid var(--border);vertical-align:middle}
        
        /* Stats Box (Warehouse Style) */
        .stats-box { background:#eff6ff; border:1px solid #dbeafe; color:#1e40af; padding:15px; border-radius:8px; margin-bottom:20px; display:flex; align-items:center; gap:30px; }
        .stat-item { display:flex; flex-direction:column; }
        .stat-label { font-size:11px; text-transform:uppercase; color:#60a5fa; font-weight:700 }
        .stat-val { font-size:20px; font-weight:600; line-height:1.2 }
        .stat-oldest { color: #dc2626; }

        /* Column Edit Dropdown */
        .col-toggler { position:relative; display:inline-block; }
        .col-dropdown { display:none; position:absolute; right:0; top:100%; background:#fff; border:1px solid #ddd; box-shadow:0 4px 6px rgba(0,0,0,0.1); padding:10px; z-index:99; min-width:150px; border-radius:6px; }
        .col-dropdown.active { display:block; }
        .col-dropdown label { display:block; padding:5px 0; cursor:pointer; font-weight:normal; }
        .col-dropdown input { width:auto; margin-right:8px; }

        /* Dashboard Widgets */
        .dash-grid{display:grid;grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));gap:20px}
        .dash-card{background:var(--white);border:1px solid var(--border);border-radius:10px;padding:20px;display:flex;flex-direction:column;justify-content:space-between;height:120px;text-decoration:none;color:inherit;transition:0.2s}
        .dash-card:hover{transform:translateY(-3px);box-shadow:0 10px 20px rgba(0,0,0,0.05)}
        .dash-card.warning{border-color:#fca5a5;background:#fef2f2}
        .dash-card.warning .dash-label{color:#ef4444}

        /* Modal */
        .modal{display:none;position:fixed;z-index:999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;backdrop-filter:blur(2px)}
        .modal-content{background:var(--white);width:95%;max-width:750px;border-radius:12px;overflow:hidden;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1)}
    </style>
    </head>
    <body>

    <div class="sidebar">
        <div class="sidebar-head"><i class="fa-solid fa-shield-halved"></i> ADMIN PANEL</div>
        
        <div class="sidebar-nav">
            <a href="<?= home_url('/b2b-panel') ?>" class="<?= get_query_var('b2b_adm_page')=='dashboard'?'active':'' ?>"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            
            <div class="sidebar-group">Ticaret</div>
            <a href="<?= home_url('/b2b-panel/orders') ?>" class="<?= get_query_var('b2b_adm_page')=='orders'?'active':'' ?>"><i class="fa-solid fa-box"></i> Siparişler</a>
            <a href="<?= home_url('/b2b-panel/products') ?>" class="<?= get_query_var('b2b_adm_page')=='products'||get_query_var('b2b_adm_page')=='product_edit'?'active':'' ?>"><i class="fa-solid fa-tags"></i> Ürünler</a>
            
            <div class="sidebar-group">B2B Yönetimi</div>
            <a href="<?= home_url('/b2b-panel/approvals') ?>" class="<?= get_query_var('b2b_adm_page')=='approvals'?'active':'' ?>">
                <i class="fa-solid fa-user-check"></i> Başvurular 
                <?php if($pending_count > 0): ?><span class="badge-count"><?= $pending_count ?></span><?php endif; ?>
            </a>
            <a href="<?= home_url('/b2b-panel/b2b-groups') ?>" class="<?= get_query_var('b2b_adm_page')=='b2b_groups'?'active':'' ?>"><i class="fa-solid fa-layer-group"></i> Gruplar & İndirim</a>
            <a href="<?= home_url('/b2b-panel/b2b-settings') ?>" class="<?= get_query_var('b2b_adm_page')=='b2b_settings'?'active':'' ?>"><i class="fa-solid fa-sliders"></i> Ayarlar</a>
            
            <div class="sidebar-group">Kullanıcılar</div>
            <a href="<?= home_url('/b2b-panel/customers') ?>" class="<?= get_query_var('b2b_adm_page')=='customers'||get_query_var('b2b_adm_page')=='customer_edit'?'active':'' ?>"><i class="fa-solid fa-users"></i> Müşteriler</a>
        </div>

        <div style="margin-top:auto;padding:20px;border-top:1px solid rgba(255,255,255,0.05)">
            <a href="<?= wp_logout_url(home_url('/b2b-login')) ?>" style="color:#fca5a5;text-decoration:none;font-weight:600;display:flex;align-items:center;gap:10px"><i class="fa-solid fa-power-off"></i> Logout</a>
        </div>
    </div>

    <div class="main">
    <?php
}

/* =====================================================
   6. PAGE: LOGIN (MASTER UI DESIGN)
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'login') return;
    
    // Zaten giriş yapmışsa yönlendir
    if (is_user_logged_in() && current_user_can('manage_options')) { 
        wp_redirect(home_url('/b2b-panel')); exit; 
    }
    
    // Form Gönderildiyse
    if ($_POST) {
        $u = wp_signon(['user_login'=>$_POST['user'], 'user_password'=>$_POST['pass'], 'remember'=>true]);
        if (!is_wp_error($u)) {
            if($u->has_cap('manage_options')) { 
                wp_redirect(home_url('/b2b-panel')); exit; 
            } else { 
                wp_logout(); 
                $err = "Access Denied. Admins only."; 
            }
        } else { 
            $err = "Invalid username or password."; 
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login | B2B Panel</title>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root {
                --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                --primary: #3b82f6; /* Admin Blue */
                --glass: rgba(255, 255, 255, 0.05);
                --border: rgba(255, 255, 255, 0.1);
                --text: #ffffff;
                --text-muted: #94a3b8;
            }
            * { box-sizing: border-box; margin: 0; padding: 0; }
            
            body {
                font-family: 'Outfit', sans-serif;
                background: var(--bg-gradient);
                color: var(--text);
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                position: relative;
            }

            /* Background FX */
            .bg-shape {
                position: absolute;
                border-radius: 50%;
                filter: blur(80px);
                z-index: -1;
                opacity: 0.4;
            }
            .shape-1 { width: 300px; height: 300px; background: var(--primary); top: -50px; left: -50px; }
            .shape-2 { width: 250px; height: 250px; background: #6366f1; bottom: -50px; right: -50px; }

            /* Login Card */
            .login-card {
                background: var(--glass);
                border: 1px solid var(--border);
                padding: 40px 30px;
                border-radius: 20px;
                width: 100%;
                max-width: 360px;
                backdrop-filter: blur(10px);
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                text-align: center;
            }

            .icon-box {
                width: 60px;
                height: 60px;
                background: rgba(59, 130, 246, 0.1);
                color: var(--primary);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                margin: 0 auto 20px;
                border: 1px solid rgba(59, 130, 246, 0.3);
            }

            h2 { font-size: 1.5rem; margin-bottom: 5px; font-weight: 700; }
            p.sub { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 30px; }

            /* Inputs */
            .input-group { margin-bottom: 15px; text-align: left; }
            label { display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 5px; margin-left: 5px;}
            
            input {
                width: 100%;
                padding: 12px 15px;
                background: rgba(0, 0, 0, 0.2);
                border: 1px solid var(--border);
                border-radius: 10px;
                color: #fff;
                font-family: inherit;
                font-size: 0.95rem;
                transition: 0.3s;
            }
            input:focus {
                outline: none;
                border-color: var(--primary);
                background: rgba(0, 0, 0, 0.3);
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
            }
            input::placeholder { color: rgba(255, 255, 255, 0.3); }

            /* Button */
            button {
                width: 100%;
                padding: 12px;
                margin-top: 10px;
                background: var(--primary);
                color: #fff;
                border: none;
                border-radius: 10px;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                transition: 0.3s;
                font-family: inherit;
            }
            button:hover {
                background: #2563eb;
                box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);
            }

            .error-msg {
                background: rgba(239, 68, 68, 0.1);
                color: #fca5a5;
                padding: 10px;
                border-radius: 8px;
                font-size: 0.85rem;
                margin-bottom: 20px;
                border: 1px solid rgba(239, 68, 68, 0.2);
            }
        </style>
    </head>
    <body>

        <div class="bg-shape shape-1"></div>
        <div class="bg-shape shape-2"></div>

        <form method="post" class="login-card">
            <div class="icon-box">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <h2>Admin Login</h2>
            <p class="sub">Enter your credentials to access the panel.</p>

            <?php if(isset($err)): ?>
                <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $err ?></div>
            <?php endif; ?>

            <div class="input-group">
                <label>Username</label>
                <input type="text" name="user" placeholder="admin" required autocomplete="off">
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="pass" placeholder="••••••••" required>
            </div>

            <button type="submit">Sign In <i class="fa-solid fa-arrow-right" style="margin-left:5px"></i></button>
        </form>

    </body>
    </html>
    <?php
    exit;
});
/* =====================================================
   7. PAGE: DASHBOARD
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'dashboard') return;
    b2b_adm_guard();
    b2b_adm_header('Dashboard');
    
    global $wpdb;
    $alert_days = 15;

    $month_sales = $wpdb->get_var($wpdb->prepare("SELECT SUM(pm.meta_value) FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON pm.post_id=p.ID WHERE p.post_status='wc-completed' AND pm.meta_key='_order_total' AND p.post_date >= %s", date('Y-m-01')));
    $total_sales = $wpdb->get_var("SELECT SUM(pm.meta_value) FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON pm.post_id=p.ID WHERE p.post_status='wc-completed' AND pm.meta_key='_order_total'");

    $statuses = wc_get_order_statuses();
    $wh_stats = [];
    foreach ($statuses as $slug => $label) {
        $clean = str_replace('wc-', '', $slug);
        $oldest = $wpdb->get_var($wpdb->prepare("SELECT post_date FROM {$wpdb->posts} WHERE post_type='shop_order' AND post_status=%s ORDER BY post_date ASC LIMIT 1", $slug));
        $count = wc_orders_count($clean);
        
        $days='-'; $late=false; $date='-';
        if ($count > 0 && $slug !== 'wc-completed' && $oldest) {
            $diff = (new DateTime())->diff(new DateTime($oldest))->days;
            $days = $diff . ' days';
            $date = date('d.m', strtotime($oldest));
            if ($diff > $alert_days) $late = true;
        }
        $wh_stats[] = ['label'=>$label, 'slug'=>$clean, 'count'=>$count, 'date'=>$date, 'days'=>$days, 'late'=>$late];
    }
    ?>

    <div class="page-header"><h1 class="page-title">Overview</h1></div>

    <div class="grid-main" style="display:grid;grid-template-columns:1fr 1fr;gap:30px;margin-bottom:30px">
        <div class="card" style="display:flex;align-items:center;justify-content:space-between">
            <div><small style="color:#6b7280;font-weight:600;text-transform:uppercase">Sales This Month</small><div style="font-size:32px;font-weight:800;color:#10b981"><?= wc_price($month_sales?:0) ?></div></div>
            <i class="fa-solid fa-chart-line" style="font-size:40px;color:#e5e7eb"></i>
        </div>
        <div class="card" style="display:flex;align-items:center;justify-content:space-between">
            <div><small style="color:#6b7280;font-weight:600;text-transform:uppercase">Total Revenue</small><div style="font-size:32px;font-weight:800;color:#0f172a"><?= wc_price($total_sales?:0) ?></div></div>
            <i class="fa-solid fa-sack-dollar" style="font-size:40px;color:#e5e7eb"></i>
        </div>
    </div>

    <h3 style="margin-bottom:20px;color:#4b5563">Order Status & Delays</h3>
    <div class="dash-grid">
        <?php foreach($wh_stats as $s): ?>
        <a href="?b2b_adm_page=orders&status=<?= $s['slug'] ?>" class="dash-card <?= $s['late']?'warning':'' ?>">
            <div style="display:flex;justify-content:space-between">
                <span class="dash-label" style="font-weight:700;font-size:12px;text-transform:uppercase;color:#6b7280"><?= $s['label'] ?></span>
                <i class="fa-solid <?= $s['slug']=='completed'?'fa-check':'fa-clock' ?>" style="color:#d1d5db"></i>
            </div>
            <div style="font-size:28px;font-weight:800;color:#1f2937"><?= $s['count'] ?></div>
            <div style="font-size:12px;color:#6b7280;border-top:1px solid #f3f4f6;padding-top:10px;display:flex;justify-content:space-between">
                <span><?= $s['date'] ?></span> <span style="font-weight:700;color:<?= $s['late']?'#ef4444':'#6b7280' ?>"><?= $s['days'] ?></span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="card" style="margin-top:30px">
        <h3 style="margin-top:0">Sales Agent Performance</h3>
        <table><thead><tr><th>Agent</th><th>Customers</th><th>Total Sales</th></tr></thead><tbody>
        <?php 
        $agents = get_users(['role'=>'sales_agent']);
        foreach($agents as $a) {
            $c_ids = get_users(['meta_key'=>'bagli_agent_id', 'meta_value'=>$a->ID, 'fields'=>'ID']);
            $ids_str = !empty($c_ids) ? implode(',', $c_ids) : '-1';
            $rev = $wpdb->get_var("SELECT SUM(pm.meta_value) FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON pm.post_id=p.ID JOIN {$wpdb->postmeta} pm_cust ON p.ID=pm_cust.post_id WHERE p.post_status='wc-completed' AND pm.meta_key='_order_total' AND pm_cust.meta_key='_customer_user' AND pm_cust.meta_value IN ($ids_str)");
            echo "<tr><td><i class='fa-solid fa-user-tie' style='margin-right:10px;color:#9ca3af'></i> {$a->display_name}</td><td>".count($c_ids)."</td><td><strong>".wc_price($rev?:0)."</strong></td></tr>";
        }
        ?>
        </tbody></table>
    </div>

    <?php b2b_adm_footer(); exit;
});

/* =====================================================
   8. PAGE: ORDERS (WAREHOUSE STYLE + WIDE MODAL)
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'orders') return;
    b2b_adm_guard();
    b2b_adm_header('Orders');

    $paged = max(1, $_GET['paged'] ?? 1);
    $status = $_GET['status'] ?? '';
    $s = $_GET['s'] ?? '';
    
    $args = ['post_type'=>'shop_order', 'post_status'=>'any', 'posts_per_page'=>20, 'paged'=>$paged, 'orderby'=>'date', 'order'=>'DESC'];
    if($status) $args['post_status'] = 'wc-'.$status;
    if($s && is_numeric($s)) $args['post__in'] = [intval($s)];
    
    $query = new WP_Query($args);
    $all_statuses = wc_get_order_statuses();

    // Stats
    global $wpdb;
    $stat_count = $query->found_posts;
    $stat_label = $status ? wc_get_order_status_name($status) : 'All Orders';
    $oldest_date = '-';
    if ($stat_count > 0 && $status != 'completed') {
        $status_sql = $status ? "AND post_status = 'wc-$status'" : "AND post_status != 'auto-draft'";
        $oldest = $wpdb->get_var("SELECT post_date FROM {$wpdb->posts} WHERE post_type='shop_order' $status_sql ORDER BY post_date ASC LIMIT 1");
        if($oldest) $oldest_date = date('d.m.Y', strtotime($oldest));
    }

    // Btn Helper
    function adm_wh_btn($oid, $wh) {
        $app = get_post_meta($oid, '_'.$wh.'_approved', true) === '1';
        $col = $app ? '#10b981' : '#ef4444'; $txt = $app ? 'Approved' : 'Pending';
        $note = esc_attr(get_post_meta($oid, '_'.$wh.'_notes', true));
        return "<button class='wh-btn' style='background:$col;width:100%;font-size:11px;padding:5px' data-id='$oid' data-wh='$wh' data-note='$note'>$txt</button>";
    }
    ?>

    <!-- WIDER MODAL CSS -->
    <style>
        .modal-content { max-width: 900px !important; } 
    </style>

    <div class="card">
        <!-- Filter Bar -->
        <div style="display:flex;gap:15px;margin-bottom:20px;flex-wrap:wrap;justify-content:space-between">
            <div style="display:flex;gap:10px">
                <select style="max-width:200px" onchange="location.href='?b2b_adm_page=orders&status='+this.value">
                    <option value="">All Statuses</option>
                    <?php foreach($all_statuses as $k=>$v): $slug=str_replace('wc-','',$k); ?><option value="<?=$slug?>" <?=selected($status,$slug)?>><?=$v?></option><?php endforeach; ?>
                </select>
                <div class="col-toggler">
                    <button type="button" class="secondary" onclick="document.querySelector('#colDrop').classList.toggle('active')"><i class="fa-solid fa-table-columns"></i> Columns</button>
                    <div id="colDrop" class="col-dropdown">
                        <label><input type="checkbox" checked data-col="0"> No</label>
                        <label><input type="checkbox" checked data-col="1"> Date</label>
                        <label><input type="checkbox" checked data-col="2"> Customer</label>
                        <label><input type="checkbox" checked data-col="3"> Wh. A</label>
                        <label><input type="checkbox" checked data-col="4"> Wh. B</label>
                        <label><input type="checkbox" checked data-col="5"> Status</label>
                        <label><input type="checkbox" checked data-col="6"> Action</label>
                    </div>
                </div>
            </div>
            <form style="display:flex;gap:10px;">
                <input name="s" placeholder="Order ID" value="<?=esc_attr($s)?>" style="margin:0;max-width:200px"><button>Search</button>
            </form>
        </div>

        <!-- Header Stats -->
        <div class="stats-box">
            <div class="stat-item"><span class="stat-label">Status</span><span class="stat-val"><?= $stat_label ?></span></div>
            <div style="width:1px;height:30px;background:#dbeafe"></div>
            <div class="stat-item"><span class="stat-label">Total Qty</span><span class="stat-val"><?= $stat_count ?></span></div>
            <?php if($oldest_date != '-'): ?>
            <div style="width:1px;height:30px;background:#dbeafe"></div>
            <div class="stat-item"><span class="stat-label">Oldest Order</span><span class="stat-val stat-oldest"><?= $oldest_date ?></span></div>
            <?php endif; ?>
        </div>

        <table id="orderTable">
            <thead><tr>
                <th data-col="0">No</th>
                <th data-col="1">Date</th>
                <th data-col="2">Customer / Address</th>
                <th data-col="3">Wh. A</th>
                <th data-col="4">Wh. B</th>
                <th data-col="5">Status</th>
                <th data-col="6" style="text-align:right">Action</th>
            </tr></thead>
            <tbody>
            <?php if($query->have_posts()): while($query->have_posts()): $query->the_post(); $oid=get_the_ID(); $o=wc_get_order($oid); if(!$o) continue; 
                $pdf_btn = ''; if (class_exists('WPO_WCPDF')) { $n = wp_create_nonce('generate_wpo_wcpdf'); $u = admin_url("admin-ajax.php?action=generate_wpo_wcpdf&document_type=packing-slip&order_ids={$oid}&_wpnonce={$n}"); $pdf_btn = '<a href="'.$u.'" target="_blank" class="button secondary" style="padding:6px 10px;border-radius:4px;color:#374151;text-decoration:none"><i class="fa-solid fa-print"></i></a>'; }
            ?>
            <tr id="row-<?=$oid?>">
                <td data-col="0">#<?=$oid?></td>
                <td data-col="1"><?=$o->get_date_created()->date('d.m H:i')?></td>
                <td data-col="2"><strong><?=$o->get_formatted_billing_full_name()?></strong><br><small style="color:#9ca3af"><?=$o->get_billing_city()?></small></td>
                <td data-col="3"><?= adm_wh_btn($oid, 'warehouse_a') ?></td>
                <td data-col="4"><?= adm_wh_btn($oid, 'warehouse_b') ?></td>
                <td data-col="5" style="width:160px">
                    <select onchange="updateStatus(<?=$oid?>, this.value)" style="padding:5px;font-size:12px;margin:0">
                        <?php foreach($all_statuses as $k=>$v): $slug=str_replace('wc-','',$k); ?><option value="<?=$slug?>" <?=selected('wc-'.$o->get_status(),$k)?>><?=$v?></option><?php endforeach; ?>
                    </select>
                </td>
                <td data-col="6" style="text-align:right;display:flex;gap:5px;justify-content:flex-end">
                    <button class="secondary" onclick="viewOrder(<?=$oid?>)" style="padding:6px 10px"><i class="fa-regular fa-eye"></i></button>
                    <?=$pdf_btn?>
                </td>
            </tr>
            <?php endwhile; else: ?><tr><td colspan="7" style="padding:20px;text-align:center">No orders found.</td></tr><?php endif; ?>
            </tbody>
        </table>
        <?php if($query->max_num_pages > 1) echo "<div style='margin-top:20px;text-align:center'>".paginate_links(['base'=>add_query_arg('paged','%#%'),'format'=>'','current'=>$paged,'total'=>$query->max_num_pages])."</div>"; ?>
    </div>

    <div id="ordModal" class="modal"><div class="modal-content"><div style="padding:15px;border-bottom:1px solid #eee;display:flex;justify-content:space-between"><h3>Details</h3><span onclick="$('#ordModal').hide()" style="cursor:pointer;font-size:20px">&times;</span></div><div id="mBody" style="padding:20px;max-height:80vh;overflow-y:auto"></div></div></div>

    <script>
    var ajaxUrl = '<?= admin_url('admin-ajax.php') ?>';
    
    // Column Toggler
    function toggleCol(idx, show) { var rows = document.getElementById('orderTable').rows; for(var i=0;i<rows.length;i++) { if(rows[i].cells.length>idx) rows[i].cells[idx].style.display=show?'':'none'; } }
    document.querySelectorAll('#colDrop input').forEach(function(cb, index){ cb.addEventListener('change', function(){ toggleCol(index, this.checked); }); });

    // Update Status
    function updateStatus(id, st) {
        if(!confirm('Update status?')) return;
        $.post(ajaxUrl, {action:'b2b_adm_update_status', order_id:id, status:st}, function(r){
            if(r.success) { 
                alert('Updated!');
                var currentFilter = '<?= $status ?>';
                if(currentFilter && currentFilter !== st) {
                    $('#row-'+id).fadeOut(500, function(){ $(this).remove(); });
                }
            } else alert('Error');
        });
    }

    // Warehouse Buttons
    $(document).on('click', '.wh-btn', function() {
        var b = $(this); var note = prompt("Admin Note:", b.data('note')); if(note===null) return;
        b.prop('disabled',true);
        $.post(ajaxUrl, {action:'b2b_adm_wh_update', order_id:b.data('id'), warehouse:b.data('wh'), note:note}, function(r){
            b.prop('disabled',false);
            if(r.success) { if(r.data.new_state) b.css('background','#10b981').text('Approved'); else b.css('background','#ef4444').text('Pending'); }
        });
    });

    // View Modal
    function viewOrder(id) {
        $('#ordModal').css('display','flex'); $('#mBody').html('Loading...');
        $.get(ajaxUrl, {action:'b2b_adm_get_details', order_id:id}, function(r){
            if(r.success) {
                var d=r.data;
                var h = `
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
                    <div style="background:#f9f9f9;padding:10px"><strong>Billing:</strong><br>${d.billing}</div>
                    <div style="background:#f9f9f9;padding:10px"><strong>Shipping:</strong><br>${d.shipping}</div>
                </div>
                
                ${d.extra_html}

                <table style="border:1px solid #eee"><thead><tr><th>Product</th><th>Qty</th></tr></thead><tbody>${d.items.map(i=>`<tr><td>${i.name}<br><small>${i.sku}</small></td><td>${i.qty}</td></tr>`).join('')}</tbody></table>
                <h3 style="text-align:right;margin-top:10px">${d.grand_total}</h3>
                `;
                $('#mBody').html(h);
            }
        });
    }
    $(window).click(function(e){if(e.target.id=='ordModal')$('#ordModal').hide();});
    </script>
    <?php b2b_adm_footer(); exit;
});
/* =====================================================
   9. PAGE: PRODUCTS (FIXED SEARCH & MENU)
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'products') return;
    b2b_adm_guard();
    
    // Search Logic
    $s = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $args = ['limit' => 20, 'paginate' => true];
    if ($s) $args['s'] = $s;
    
    $products = wc_get_products($args);
    b2b_adm_header('Product Management');
    ?>
    <div class="page-header"><h1 class="page-title">Products</h1></div>
    <div class="card">
        <div style="display:flex;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
            <div class="col-toggler">
                <button type="button" class="secondary" onclick="document.querySelector('#pColDrop').classList.toggle('active')"><i class="fa-solid fa-table-columns"></i> Columns</button>
                <div id="pColDrop" class="col-dropdown">
                    <label><input type="checkbox" checked data-col="0"> Image</label>
                    <label><input type="checkbox" checked data-col="1"> Name / SKU</label>
                    <label><input type="checkbox" checked data-col="2"> Price</label>
                    <label><input type="checkbox" checked data-col="3"> Stock</label>
                    <label><input type="checkbox" checked data-col="4"> Action</label>
                </div>
            </div>
            
            <!-- FIXED SEARCH FORM -->
            <form style="display:flex;gap:10px" method="get" action="<?= home_url('/') ?>">
                <!-- Bu satır formun doğru rewrite kuralına gitmesini sağlar -->
                <input type="hidden" name="b2b_adm_page" value="products"> 
                <input name="s" value="<?= esc_attr($s) ?>" placeholder="Search product..." style="margin:0;max-width:300px">
                <button>Search</button>
                <?php if($s): ?><a href="<?= home_url('/b2b-panel/products') ?>" style="padding:10px;color:#ef4444;text-decoration:none">Reset</a><?php endif; ?>
            </form>
        </div>
        
        <table id="prodTable">
            <thead><tr><th data-col="0">Image</th><th data-col="1">Name / SKU</th><th data-col="2">Price</th><th data-col="3">Stock</th><th data-col="4">Action</th></tr></thead>
            <tbody>
            <?php if(empty($products->products)): ?>
                <tr><td colspan="5" style="text-align:center;padding:20px;color:#999">No products found.</td></tr>
            <?php else: foreach ($products->products as $p): $img=wp_get_attachment_image_src($p->get_image_id(),'thumbnail'); ?>
            <tr>
                <td data-col="0"><img src="<?=$img?$img[0]:'https://via.placeholder.com/40'?>" style="width:40px;border-radius:4px"></td>
                <td data-col="1"><strong><?=$p->get_name()?></strong><br><small style="color:#9ca3af"><?=$p->get_sku()?></small></td>
                <td data-col="2"><?=$p->get_price_html()?></td>
                <td data-col="3"><?=$p->managing_stock()?$p->get_stock_quantity():($p->is_in_stock()?'In Stock':'Out')?></td>
                <td data-col="4"><a href="<?=home_url('/b2b-panel/products/edit?id='.$p->get_id())?>"><button class="secondary" style="padding:6px 12px">Edit</button></a></td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if($products->max_num_pages > 1): ?>
        <div style="margin-top:20px;text-align:center;display:flex;justify-content:center;gap:5px">
            <?php 
            $current = max(1, get_query_var('paged'));
            echo paginate_links([
                'base' => home_url('/b2b-panel/products%_%'),
                'format' => '&paged=%#%',
                'current' => $current,
                'total' => $products->max_num_pages,
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'type' => 'plain'
            ]); 
            ?>
        </div>
        <?php endif; ?>
    </div>
    <script>
    function toggleColP(idx, show) { var rows = document.getElementById('prodTable').rows; for(var i=0;i<rows.length;i++) { if(rows[i].cells.length>idx) rows[i].cells[idx].style.display=show?'':'none'; } }
    document.querySelectorAll('#pColDrop input').forEach(function(cb, i){ cb.addEventListener('change', function(){ toggleColP(i, this.checked); }); });
    </script>
    <?php b2b_adm_footer(); exit;
});
/* =====================================================
   10. PAGE: PRODUCT EDIT (FULL STOCK LOGIC FIXED)
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'product_edit') return;
    b2b_adm_guard();
    
    $id = intval($_GET['id']);
    $p = wc_get_product($id);
    
    if(!$p) wp_die('Product not found');
    $is_variable = $p->is_type('variable');

    // --- SAVE PROCESS ---
    if($_POST) {
        // 1. LOGGING (Parent Level)
        if($_POST['price'] != $p->get_regular_price()) b2b_adm_add_log($id, 'price', $p->get_regular_price(), $_POST['price'], 'Main Price Update');
        if($_POST['stock_qty'] != $p->get_stock_quantity()) b2b_adm_add_log($id, 'stock', $p->get_stock_quantity(), $_POST['stock_qty'], 'Main Stock Update');

        // 2. COMMON FIELDS (Title, Desc, Slug)
        wp_update_post([
            'ID' => $id, 
            'post_status' => $_POST['status'], 
            'post_excerpt' => wp_kses_post($_POST['short_desc']), 
            'post_content' => wp_kses_post($_POST['long_desc'])
        ]);

        // 3. META DATA (SKU, Price - Parent)
        update_post_meta($id, '_sku', wc_clean($_POST['sku']));
        
        // Fiyatlar (Varyasyonlu ise parent fiyatı genelde pasiftir ama kaydediyoruz)
        update_post_meta($id, '_regular_price', wc_clean($_POST['price']));
        update_post_meta($id, '_price', wc_clean($_POST['price']));

        // 4. PARENT STOCK MANAGEMENT (Global Stock for Variations)
        // Bu bölüm artık hem basit hem varyasyonlu ürünler için çalışır.
        $m = isset($_POST['manage_stock']) ? 'yes' : 'no'; 
        update_post_meta($id, '_manage_stock', $m);
        if($m == 'yes') {
            update_post_meta($id, '_stock', wc_clean($_POST['stock_qty'])); 
        } else {
            update_post_meta($id, '_stock_status', $_POST['stock_status']);
        }

        // 5. CATEGORIES
        $cats = isset($_POST['cats']) ? array_map('intval', $_POST['cats']) : []; 
        wp_set_object_terms($id, $cats, 'product_cat');
        
        // 6. ASSEMBLY & VARIATIONS
        update_post_meta($id, '_assembly_enabled', isset($_POST['assembly'])?'yes':'no');
        update_post_meta($id, '_assembly_price', wc_clean($_POST['assembly_price']));
        update_post_meta($id, '_assembly_tax', $_POST['assembly_tax']);

        if ($is_variable && isset($_POST['vars'])) {
            foreach ($_POST['vars'] as $vid => $vdata) {
                $var_obj = wc_get_product($vid);
                if(!$var_obj) continue;

                // Var Log
                if ($vdata['price'] != $var_obj->get_regular_price()) b2b_adm_add_log($id, 'var_price', $var_obj->get_regular_price(), $vdata['price'], "Var #$vid Price");
                
                update_post_meta($vid, '_regular_price', wc_clean($vdata['price']));
                update_post_meta($vid, '_price', wc_clean($vdata['price']));
                
                $v_manage = isset($vdata['manage']) ? 'yes' : 'no';
                update_post_meta($vid, '_manage_stock', $v_manage);
                
                if ($v_manage == 'yes') {
                    if ($vdata['qty'] != $var_obj->get_stock_quantity()) b2b_adm_add_log($id, 'var_stock', $var_obj->get_stock_quantity(), $vdata['qty'], "Var #$vid Stock");
                    update_post_meta($vid, '_stock', wc_clean($vdata['qty']));
                } else {
                    update_post_meta($vid, '_stock_status', $vdata['status']);
                }
                wc_delete_product_transients($vid);
            }
            WC_Product_Variable::sync($id);
        }
        
        $p = wc_get_product($id); // Refresh
        echo '<div style="background:#d1fae5;color:#065f46;padding:15px;margin-bottom:20px;border-radius:8px;border:1px solid #a7f3d0">Changes saved successfully.</div>';
    }

    $all_cats = get_terms(['taxonomy'=>'product_cat', 'hide_empty'=>false]);
    $cur_cats = wp_get_post_terms($id, 'product_cat', ['fields'=>'ids']);
    $logs = get_post_meta($id, '_b2b_stock_log', true) ?: [];

    b2b_adm_header('Edit: ' . $p->get_name());
    ?>
    
    <style>
        .grid-edit { display: grid; grid-template-columns: 3fr 1fr; gap: 25px; align-items: start; }
        .edit-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .edit-card h3 { margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #f3f4f6; font-size: 15px; color: #111827; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .cat-wrapper { max-height: 250px; overflow-y: auto; background: #f9fafb; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; }
        .cat-row { display: flex; align-items: center; gap: 8px; padding: 4px 0; font-size: 13px; color: #374151; cursor: pointer; }
        .cat-row input { margin: 0; width: 16px; height: 16px; }
        
        .var-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .var-table th { background: #f3f4f6; text-align: left; padding: 10px; border-bottom: 2px solid #e5e7eb; }
        .var-table td { padding: 10px; border-bottom: 1px solid #e5e7eb; vertical-align: middle; }
        .var-input { width: 80px; padding: 6px; border: 1px solid #d1d5db; border-radius: 4px; }
        
        @media(max-width:900px) { .grid-edit { grid-template-columns: 1fr; } }
    </style>

    <div style="margin-bottom:20px;display:flex;justify-content:space-between;align-items:center">
        <a href="<?= home_url('/b2b-panel/products') ?>" style="text-decoration:none"><button class="secondary"><i class="fa-solid fa-arrow-left"></i> Back to Products</button></a>
        <span style="background:<?= $is_variable?'#fef3c7':'#d1fae5' ?>;color:<?= $is_variable?'#92400e':'#065f46' ?>;padding:5px 12px;border-radius:20px;font-size:12px;font-weight:700">
            <?= $is_variable ? 'VARIABLE PRODUCT' : 'SIMPLE PRODUCT' ?>
        </span>
    </div>

    <form method="post" class="grid-edit">
        <!-- LEFT COLUMN -->
        <div>
            <!-- GENERAL INFO -->
            <div class="edit-card">
                <h3>General Information</h3>
                <div style="margin-bottom:15px"><label>Product Name</label><input type="text" value="<?= esc_attr($p->get_name()) ?>" disabled style="background:#f3f4f6;color:#6b7280;cursor:not-allowed"></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                    <div><label>SKU</label><input type="text" name="sku" value="<?= $p->get_sku() ?>"></div>
                    <?php if (!$is_variable): ?>
                        <div><label>Regular Price</label><input type="number" step="0.01" name="price" value="<?= $p->get_regular_price() ?>"></div>
                    <?php else: ?>
                        <div><label>Base Price (Optional)</label><input type="number" step="0.01" name="price" value="<?= $p->get_regular_price() ?>"></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- PARENT STOCK MANAGEMENT (Available for ALL types now) -->
            <div class="edit-card">
                <h3>Inventory (Global / Parent)</h3>
                <div style="background:#f0f9ff;padding:10px;border-left:4px solid #0ea5e9;margin-bottom:15px;font-size:12px;color:#0369a1">
                    <?= $is_variable ? 'For variables: This stock is used if a variation does NOT manage its own stock.' : 'Main stock for this simple product.' ?>
                </div>
                
                <label style="display:flex;align-items:center;gap:10px;margin-bottom:20px;padding:10px;background:#f9fafb;border-radius:6px;border:1px solid #e5e7eb;cursor:pointer">
                    <input type="checkbox" name="manage_stock" <?= checked($p->managing_stock(), true, false) ?> style="width:20px;height:20px;margin:0"> 
                    <span>Enable Stock Management (Parent Level)</span>
                </label>
                
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                    <div><label>Stock Quantity</label><input type="number" name="stock_qty" value="<?= $p->get_stock_quantity() ?>"></div>
                    <div><label>Stock Status</label>
                        <select name="stock_status">
                            <option value="instock" <?= selected($p->get_stock_status(),'instock') ?>>In Stock</option>
                            <option value="outofstock" <?= selected($p->get_stock_status(),'outofstock') ?>>Out of Stock</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- VARIATIONS TABLE (Only if Variable) -->
            <?php if ($is_variable): ?>
            <div class="edit-card">
                <h3>Variations</h3>
                <?php $variations = $p->get_children(); 
                if (empty($variations)) { echo '<p style="color:red">No variations created yet.</p>'; } else { ?>
                <div style="overflow-x:auto">
                    <table class="var-table">
                        <thead><tr><th>Attributes</th><th>Price</th><th>Manage Stock</th><th>Qty</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($variations as $vid): 
                            $v = wc_get_product($vid);
                            $attrs = []; foreach($v->get_attributes() as $k=>$val) $attrs[] = ucfirst(str_replace('pa_','',$k)).': <b>'.$val.'</b>';
                        ?>
                        <tr>
                            <td><small>#<?= $vid ?></small><br><?= implode(', ', $attrs) ?></td>
                            <td><input type="number" step="0.01" name="vars[<?= $vid ?>][price]" value="<?= $v->get_regular_price() ?>" class="var-input" style="width:80px"></td>
                            <td style="text-align:center"><input type="checkbox" name="vars[<?= $vid ?>][manage]" value="yes" <?= checked($v->managing_stock(), true, false) ?>></td>
                            <td><input type="number" name="vars[<?= $vid ?>][qty]" value="<?= $v->get_stock_quantity() ?>" class="var-input"></td>
                            <td><select name="vars[<?= $vid ?>][status]" style="padding:5px;font-size:12px"><option value="instock" <?= selected($v->get_stock_status(),'instock') ?>>In</option><option value="outofstock" <?= selected($v->get_stock_status(),'outofstock') ?>>Out</option></select></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php } ?>
            </div>
            <?php endif; ?>

            <div class="edit-card">
                <h3>Descriptions</h3>
                <label>Short Description</label><textarea name="short_desc" style="height:80px"><?= esc_textarea($p->get_short_description()) ?></textarea>
                <label>Long Description</label><textarea name="long_desc" style="height:200px"><?= esc_textarea($p->get_description()) ?></textarea>
            </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div>
            <div class="edit-card" style="border-top: 3px solid #2563eb">
                <h3>Publish</h3>
                <label>Status</label>
                <select name="status" style="margin-bottom:15px"><option value="publish" <?= selected($p->get_status(),'publish') ?>>Active</option><option value="draft" <?= selected($p->get_status(),'draft') ?>>Draft</option></select>
                <button style="width:100%;padding:12px">Save Changes</button>
            </div>

            <div class="edit-card">
                <h3>Categories</h3>
                <div class="cat-wrapper"><?php foreach($all_cats as $cat): ?><label class="cat-row"><input type="checkbox" name="cats[]" value="<?= $cat->term_id ?>" <?= in_array($cat->term_id, $cur_cats)?'checked':'' ?>> <?= esc_html($cat->name) ?></label><?php endforeach; ?></div>
            </div>

            <div class="edit-card">
                <h3>Extra Services</h3>
                <label style="display:flex;align-items:center;gap:10px;margin-bottom:10px"><input type="checkbox" name="assembly" <?= checked(get_post_meta($id,'_assembly_enabled',true),'yes',false) ?> style="width:18px;height:18px;margin:0"> Assembly Service</label>
                <input type="number" name="assembly_price" placeholder="Price" value="<?= get_post_meta($id,'_assembly_price',true) ?>" step="0.01">
                <div style="margin-top:10px"><label>Tax</label><select name="assembly_tax"><option value="no">Excluded</option><option value="yes" <?= selected(get_post_meta($id,'_assembly_tax',true),'yes',false) ?>>Included</option></select></div>
            </div>

            <div class="edit-card">
                <h3>History</h3>
                <div style="max-height:200px;overflow-y:auto;font-size:12px;color:#666">
                    <?php if(empty($logs)): echo "No logs."; else: foreach($logs as $l): ?>
                    <div style="border-bottom:1px solid #eee;padding:8px 0"><span style="float:right;color:#9ca3af;font-size:10px"><?= date('d M H:i', strtotime($l['date'])) ?></span><strong><?= $l['type'] ?></strong><br><?= $l['user'] ?>: <span style="text-decoration:line-through;color:#ef4444"><?= $l['old'] ?></span> &rarr; <span style="font-weight:bold;color:#10b981"><?= $l['new'] ?></span><div style="color:#aaa;font-size:10px"><?=$l['msg']?></div></div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </form>
    <?php b2b_adm_footer(); exit;
});
				  
/* =====================================================
   11. PAGE: CUSTOMERS (B2BKING FIXED)
===================================================== */
add_action('template_redirect', function () {
    $page = get_query_var('b2b_adm_page');
    if (!in_array($page, ['customers', 'customer_edit'])) return;
    b2b_adm_guard();

    // -- CUSTOMER LIST --
    if ($page === 'customers') {
        $paged = max(1, $_GET['paged'] ?? 1);
        $s = isset($_GET['s']) ? trim($_GET['s']) : '';
        $number = 20;
        
        $args = [
            'role__in' => ['customer', 'subscriber', 'sales_agent'], 
            'number'   => $number,
            'offset'   => ($paged - 1) * $number,
            'search'   => $s ? "*{$s}*" : '',
            'orderby'  => 'registered',
            'order'    => 'DESC'
        ];
        
        $user_query = new WP_User_Query($args);
        $users = $user_query->get_results();
        $total_users = $user_query->get_total();
        $total_pages = ceil($total_users / $number);

        b2b_adm_header('Customer Management');
        ?>
        <div class="page-header"><h1 class="page-title">Customers</h1></div>
        
        <div class="card">
            <!-- Toolbar -->
            <div style="display:flex;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:15px;align-items:center">
                <div class="col-toggler">
                    <button type="button" class="secondary" onclick="document.querySelector('#cColDrop').classList.toggle('active')"><i class="fa-solid fa-table-columns"></i> Columns</button>
                    <div id="cColDrop" class="col-dropdown">
                        <label><input type="checkbox" checked data-col="0"> ID</label>
                        <label><input type="checkbox" checked data-col="1"> Customer Info</label>
                        <label><input type="checkbox" checked data-col="2"> Contact</label>
                        <label><input type="checkbox" checked data-col="3"> B2B Group</label>
                        <label><input type="checkbox" checked data-col="4"> Location</label>
                        <label><input type="checkbox" checked data-col="5"> Role</label>
                        <label><input type="checkbox" checked data-col="6"> Actions</label>
                    </div>
                </div>

                <div style="flex:1;display:flex;justify-content:flex-end;gap:10px">
                    <span style="align-self:center;font-size:12px;color:#6b7280;margin-right:10px">Total: <strong><?= $total_users ?></strong></span>
                    <form style="display:flex;gap:5px">
                        <input name="s" value="<?= esc_attr($s) ?>" placeholder="Search customers..." style="margin:0;max-width:250px">
                        <button>Search</button>
                        <?php if($s): ?><a href="<?= home_url('/b2b-panel/customers') ?>" style="padding:10px;color:#ef4444;text-decoration:none">Reset</a><?php endif; ?>
                    </form>
                </div>
            </div>
            
            <table id="custTable">
                <thead><tr>
                    <th data-col="0">ID</th>
                    <th data-col="1">Customer</th>
                    <th data-col="2">Contact</th>
                    <th data-col="3">B2B Group</th>
                    <th data-col="4">Location</th>
                    <th data-col="5">Role</th>
                    <th data-col="6" style="text-align:right">Action</th>
                </tr></thead>
                <tbody>
                <?php if(empty($users)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:#999">No customers found.</td></tr>
                <?php else: foreach ($users as $u): 
                    $phone = get_user_meta($u->ID, 'billing_phone', true);
                    $city = get_user_meta($u->ID, 'billing_city', true);
                    $country = get_user_meta($u->ID, 'billing_country', true);
                    
                    // --- B2BKING FIX ---
                    $group_id = get_user_meta($u->ID, 'b2bking_customergroup', true); 
                    $group_name = '-';
                    $is_b2b = false;

                    if ($group_id && $group_id !== 'b2cuser') {
                        $group_post = get_post($group_id);
                        if ($group_post) {
                            $group_name = $group_post->post_title;
                            $is_b2b = true;
                        }
                    } elseif ($group_id === 'b2cuser') {
                        $group_name = 'B2C User';
                    }
                    
                    $role_bg = in_array('sales_agent', $u->roles) ? '#dbeafe' : '#f3f4f6';
                    $role_col = in_array('sales_agent', $u->roles) ? '#1e40af' : '#374151';
                ?>
                <tr>
                    <td data-col="0">#<?= $u->ID ?></td>
                    <td data-col="1">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:35px;height:35px;background:#f3f4f6;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:14px">
                                <?= strtoupper(substr($u->display_name,0,1)) ?>
                            </div>
                            <div>
                                <strong style="color:#111827"><?= esc_html($u->display_name) ?></strong><br>
                                <small style="color:#9ca3af">@<?= $u->user_login ?></small>
                            </div>
                        </div>
                    </td>
                    <td data-col="2">
                        <div style="font-size:12px;line-height:1.4">
                            <i class="fa-regular fa-envelope" style="width:15px;color:#9ca3af"></i> <?= esc_html($u->user_email) ?><br>
                            <i class="fa-solid fa-phone" style="width:15px;color:#9ca3af"></i> <?= esc_html($phone ?: '-') ?>
                        </div>
                    </td>
                    <td data-col="3">
                        <span style="background:<?= $is_b2b ? '#d1fae5' : '#f3f4f6' ?>;color:<?= $is_b2b ? '#065f46' : '#6b7280' ?>;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:600">
                            <?= esc_html($group_name) ?>
                        </span>
                    </td>
                    <td data-col="4">
                        <small style="color:#6b7280">
                            <?= $city ? "$city, $country" : '-' ?>
                        </small>
                    </td>
                    <td data-col="5">
                        <span style="background:<?= $role_bg ?>;color:<?= $role_col ?>;padding:3px 8px;border-radius:4px;font-size:10px;font-weight:700;text-transform:uppercase">
                            <?= !empty($u->roles) ? $u->roles[0] : 'Guest' ?>
                        </span>
                    </td>
                    <td data-col="6" style="text-align:right">
                        <a href="<?= home_url('/b2b-panel/customers/edit?id='.$u->ID) ?>">
                            <button class="secondary" style="padding:6px 12px;font-size:12px">Edit</button>
                        </a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>

            <?php if($total_pages > 1): ?>
            <div style="margin-top:20px;text-align:center;display:flex;justify-content:center;gap:5px">
                <?php echo paginate_links(['base'=>add_query_arg('paged','%#%'),'format'=>'','current'=>$paged,'total'=>$total_pages,'prev_text'=>'&laquo;','next_text'=>'&raquo;','type'=>'plain']); ?>
            </div>
            <?php endif; ?>
        </div>
        
        <script>
        function toggleColC(idx, show) { var rows = document.getElementById('custTable').rows; for(var i=0;i<rows.length;i++) { if(rows[i].cells.length>idx) rows[i].cells[idx].style.display=show?'':'none'; } }
        document.querySelectorAll('#cColDrop input').forEach(function(cb, i){ cb.addEventListener('change', function(){ toggleColC(i, this.checked); }); });
        </script>
        <?php
        b2b_adm_footer(); exit;
    }

    // -- CUSTOMER EDIT --
    if ($page === 'customer_edit') {
        $id = intval($_GET['id']);
        $u = get_userdata($id);
        if (!$u) wp_die('User not found');

        // SAVE PROCESS
        if ($_POST) {
            // Core WP Fields
            wp_update_user([
                'ID' => $id,
                'first_name' => sanitize_text_field($_POST['first_name']),
                'last_name'  => sanitize_text_field($_POST['last_name']),
                'user_email' => sanitize_email($_POST['email']),
                'display_name' => sanitize_text_field($_POST['first_name'] . ' ' . $_POST['last_name'])
            ]);

            // Billing
            update_user_meta($id, 'billing_first_name', sanitize_text_field($_POST['first_name']));
            update_user_meta($id, 'billing_last_name', sanitize_text_field($_POST['last_name']));
            update_user_meta($id, 'billing_phone', sanitize_text_field($_POST['phone']));
            update_user_meta($id, 'billing_company', sanitize_text_field($_POST['company']));
            update_user_meta($id, 'billing_address_1', sanitize_text_field($_POST['address_1']));
            update_user_meta($id, 'billing_city', sanitize_text_field($_POST['city']));
            update_user_meta($id, 'billing_postcode', sanitize_text_field($_POST['postcode']));
            
            // Shipping
            update_user_meta($id, 'shipping_address_1', sanitize_text_field($_POST['s_address_1']));
            update_user_meta($id, 'shipping_city', sanitize_text_field($_POST['s_city']));
            update_user_meta($id, 'shipping_postcode', sanitize_text_field($_POST['s_postcode']));
            update_user_meta($id, 'shipping_company', sanitize_text_field($_POST['s_company']));
            
            // Agent
            if(isset($_POST['assigned_agent'])) update_user_meta($id, 'bagli_agent_id', intval($_POST['assigned_agent']));

            // B2BKing Group Save
            if(isset($_POST['b2b_group'])) {
                $grp = sanitize_text_field($_POST['b2b_group']);
                update_user_meta($id, 'b2bking_customergroup', $grp); 
                
                // Set b2buser status
                $is_b2b_val = ($grp !== 'b2cuser' && $grp !== '') ? 'yes' : 'no';
                update_user_meta($id, 'b2bking_b2buser', $is_b2b_val);
            }

            // Password
            if (!empty($_POST['new_pass'])) wp_set_password($_POST['new_pass'], $id);

            $u = get_userdata($id); // Refresh
            echo '<div style="background:#d1fae5;color:#065f46;padding:15px;margin-bottom:20px;border-radius:8px">Customer updated successfully.</div>';
        }

        // Data Prep
        $agent_val = get_user_meta($id, 'bagli_agent_id', true);
        $agents = get_users(['role__in' => ['sales_agent', 'administrator'], 'fields' => ['ID', 'display_name']]);
        
        // B2BKing Data
        $current_group = get_user_meta($id, 'b2bking_customergroup', true);
        $b2b_groups = get_posts(['post_type' => 'b2bking_group', 'numberposts' => -1, 'post_status' => 'publish']);

        b2b_adm_header('Edit Customer');
        ?>
        <div style="margin-bottom:20px"><a href="<?= home_url('/b2b-panel/customers') ?>"><button class="secondary">&laquo; Back to List</button></a></div>
        
        <form method="post" class="grid-main" style="grid-template-columns:3fr 1fr;gap:25px">
            <!-- LEFT -->
            <div style="display:flex;flex-direction:column;gap:20px">
                <div class="card">
                    <h3 style="margin-top:0;border-bottom:1px solid #eee;padding-bottom:10px;color:#111827"><i class="fa-solid fa-user"></i> Personal Information</h3>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                        <div><label>First Name</label><input type="text" name="first_name" value="<?= esc_attr($u->first_name) ?>"></div>
                        <div><label>Last Name</label><input type="text" name="last_name" value="<?= esc_attr($u->last_name) ?>"></div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                        <div><label>Email Address</label><input type="email" name="email" value="<?= esc_attr($u->user_email) ?>"></div>
                        <div><label>Phone Number</label><input type="text" name="phone" value="<?= esc_attr(get_user_meta($id, 'billing_phone', true)) ?>"></div>
                    </div>
                </div>

                <div class="card">
                    <h3 style="margin-top:0;border-bottom:1px solid #eee;padding-bottom:10px;color:#111827"><i class="fa-solid fa-map-pin"></i> Billing Address</h3>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                        <div><label>Company</label><input type="text" name="company" value="<?= esc_attr(get_user_meta($id, 'billing_company', true)) ?>"></div>
                        <div><label>Postcode</label><input type="text" name="postcode" value="<?= esc_attr(get_user_meta($id, 'billing_postcode', true)) ?>"></div>
                    </div>
                    <label>Address</label><input type="text" name="address_1" value="<?= esc_attr(get_user_meta($id, 'billing_address_1', true)) ?>">
                    <label>City</label><input type="text" name="city" value="<?= esc_attr(get_user_meta($id, 'billing_city', true)) ?>">
                </div>

                <div class="card">
                    <h3 style="margin-top:0;border-bottom:1px solid #eee;padding-bottom:10px;color:#111827"><i class="fa-solid fa-truck"></i> Shipping Address</h3>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                        <div><label>Company</label><input type="text" name="s_company" value="<?= esc_attr(get_user_meta($id, 'shipping_company', true)) ?>"></div>
                        <div><label>Postcode</label><input type="text" name="s_postcode" value="<?= esc_attr(get_user_meta($id, 'shipping_postcode', true)) ?>"></div>
                    </div>
                    <label>Address</label><input type="text" name="s_address_1" value="<?= esc_attr(get_user_meta($id, 'shipping_address_1', true)) ?>">
                    <label>City</label><input type="text" name="s_city" value="<?= esc_attr(get_user_meta($id, 'shipping_city', true)) ?>">
                </div>
            </div>

            <!-- RIGHT -->
            <div style="display:flex;flex-direction:column;gap:20px">
                <div class="card" style="border-top:3px solid var(--accent)">
                    <h3 style="margin-top:0;color:#111827">Actions</h3>
                    <button style="width:100%;padding:12px">Save Customer</button>
                </div>

                <!-- B2BKing Group Card (Fixed) -->
                <div class="card">
                    <h3 style="margin-top:0;color:#111827">B2B Group</h3>
                    <label>Customer Group</label>
                    <select name="b2b_group">
                        <option value="b2cuser" <?= selected($current_group, 'b2cuser') ?>>B2C User (Default)</option>
                        <?php foreach($b2b_groups as $bg): ?>
                            <option value="<?= $bg->ID ?>" <?= selected($current_group, $bg->ID) ?>><?= esc_html($bg->post_title) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p style="font-size:11px;color:#6b7280;margin-top:5px;line-height:1.4">Select the B2BKing group to apply pricing rules.</p>
                </div>

                <div class="card">
                    <h3 style="margin-top:0;color:#111827">Sales Agent</h3>
                    <label>Assigned To</label>
                    <select name="assigned_agent">
                        <option value="">-- None --</option>
                        <?php foreach ($agents as $a): ?>
                            <option value="<?= $a->ID ?>" <?= selected($agent_val, $a->ID) ?>><?= esc_html($a->display_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p style="font-size:12px;color:#6b7280;margin-top:8px;line-height:1.4">Orders will be visible to the selected agent.</p>
                </div>

                <div class="card" style="background:#fef2f2;border-color:#fca5a5">
                    <h3 style="margin-top:0;color:#ef4444">Security</h3>
                    <label>New Password</label>
                    <input type="text" name="new_pass" placeholder="Leave empty to keep">
                </div>
            </div>
        </form>
        <?php
        b2b_adm_footer(); exit;
    }
});		   /* =====================================================
  /* =====================================================
   X. FORCE ERP MODE (TAM KAPANMA & GLOBAL ROUTER)
   Tüm panelleri tanır, harici linkleri Master sayfasına atar.
===================================================== */
add_action('template_redirect', function() {
    
    // 1. Teknik İstekleri Yoksay (AJAX, Cron, API, Robots.txt vb.)
    if (defined('DOING_AJAX') && DOING_AJAX) return;
    if (defined('DOING_CRON') && DOING_CRON) return;
    if (strpos($_SERVER['REQUEST_URI'], '/wp-json/') !== false) return;
    if (is_admin()) return; // /wp-admin klasörüne erişimi engelleme (Adminler için)

    // 2. Hangi Paneldeyiz? (Query Var Kontrolü)
    // Bu değişkenler önceki snippetlarda tanımladığımız URL yapılarıdır.
    $is_b2b       = get_query_var('b2b_adm_page');      // Admin Paneli
    $is_sales     = get_query_var('sales_panel') || get_query_var('sales_login'); // Sales Agent Paneli
    $is_warehouse = get_query_var('wh_panel') || get_query_var('wh_login');       // Warehouse Paneli
    $is_master    = get_query_var('master_portal');     // Ana Giriş Kapısı

    // 3. Geçerli bir ERP sayfasında mıyız?
    if ($is_b2b || $is_sales || $is_warehouse || $is_master) {
        // Evet, geçerli bir panel sayfasındayız. 
        // Müdahale etme, bırak ilgili panelin kendi güvenlik önlemi (Guard fonksiyonu) devreye girsin.
        return;
    }

    // 4. Panel Dışı Bir Yer (Örn: Anasayfa, Merhaba Dünya yazısı, 404)
    // Kullanıcıyı Master Portal'a yönlendir.
   // wp_redirect(home_url('/b2b-master'));
    //exit;
});


lütfen projeye b2b müşterileri detayına ödeme yöntemş yetkileri ekle. hepsi ingilizce olsun kodun. 