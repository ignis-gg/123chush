<?php
/**
 * Idempotent content sync of "правки по сайту Коваль.docx" batch, applied
 * on ddev on 2026-09-12, onto production koval-legal.pp.ua.
 * Run: wp eval-file sync-docx-edits.php
 * Safe to re-run: every write checks post_name/slug first, and every
 * change is either an exact-string diff (skips if already applied) or an
 * explicit final value.
 */

function rr($v, $pairs) {
    if (is_string($v)) {
        foreach ($pairs as $old => $new) $v = str_replace($old, $new, $v);
        return $v;
    }
    if (is_array($v)) {
        foreach ($v as $k => $vv) $v[$k] = rr($vv, $pairs);
        return $v;
    }
    return $v;
}

function sync_post_fields($post_id, $pairs, $expected_post_name) {
    $post = get_post($post_id);
    if (!$post) { echo "SKIP: post $post_id not found\n"; return; }
    if ($post->post_name !== $expected_post_name) {
        echo "WARN: post $post_id post_name mismatch: got '{$post->post_name}', expected '$expected_post_name' — SKIPPED for safety\n";
        return;
    }
    $updates = [];
    foreach (['post_title', 'post_content', 'post_excerpt'] as $f) {
        $new = rr($post->$f, $pairs);
        if ($new !== $post->$f) $updates[$f] = $new;
    }
    if ($updates) {
        $updates['ID'] = $post_id;
        wp_update_post($updates);
        echo "Updated post $post_id ($expected_post_name) fields: " . implode(',', array_keys($updates)) . "\n";
    }
    $meta = get_post_meta($post_id);
    foreach ($meta as $key => $vals) {
        foreach ($vals as $raw) {
            $val = maybe_unserialize($raw);
            $new_val = rr($val, $pairs);
            if ($new_val !== $val) {
                update_post_meta($post_id, $key, $new_val, $val);
                echo "Updated post $post_id ($expected_post_name) meta '$key'\n";
            }
        }
    }
}

function set_meta_if_changed($post_id, $expected_post_name, $key, $final_value) {
    $post = get_post($post_id);
    if (!$post) { echo "SKIP: post $post_id not found\n"; return; }
    if ($post->post_name !== $expected_post_name) {
        echo "WARN: post $post_id post_name mismatch: got '{$post->post_name}', expected '$expected_post_name' — SKIPPED for safety\n";
        return;
    }
    $current = get_post_meta($post_id, $key, true);
    if ($current === $final_value) {
        echo "OK (already correct): post $post_id ($expected_post_name) meta '$key'\n";
        return;
    }
    update_post_meta($post_id, $key, $final_value);
    echo "SET: post $post_id ($expected_post_name) meta '$key'\n";
}

echo "=== 1. Global text pairs across specific posts ===\n";
$global_pairs = [
    'Шлюбний контракт' => 'Шлюбний договір',
    'шлюбний контракт' => 'шлюбний договір',
];
sync_post_fields(138, $global_pairs, 'shlyubnyy-kontrakt');
sync_post_fields(123, $global_pairs, 'simeyni-vidnosyny');
sync_post_fields(44, $global_pairs, 'tsiny');

echo "=== 2. Term (service_category 25) description ===\n";
$term = get_term(25, 'service_category');
if (is_wp_error($term) || !$term) {
    echo "WARN: service_category term 25 not found — SKIPPED\n";
} elseif ($term->name !== 'Сімейні відносини') {
    echo "WARN: term 25 name mismatch: got '{$term->name}' — SKIPPED for safety\n";
} else {
    $new_desc = rr($term->description, $global_pairs);
    if ($new_desc !== $term->description) {
        wp_update_term(25, 'service_category', ['description' => $new_desc]);
        echo "Updated term 25 description\n";
    } else {
        echo "OK (already correct): term 25 description\n";
    }
}

echo "=== 3. Home page (post 7) lead + poslugy hero text ===\n";
sync_post_fields(7, ['апостилю документів' => 'апостилювання документів'], 'golovna');
// poslugy hero string is a simple str_replace on whatever page/meta holds it.
sync_post_fields(7, ['з фіксованою вартістю та строком' => 'із фіксованою вартістю та строками'], 'golovna');

echo "=== 4. Privacy policy (post 3) ===\n";
sync_post_fields(3, ['заповнюючи' => 'заповнивши'], 'privacy-policy');

echo "=== 5. Post 144 catalog_short_description ===\n";
set_meta_if_changed(144, 'kopiya-sudovogo-rishennya', 'catalog_short_description',
    'Отримання засвідчених копій судових рішень для подання до державних органів.');

echo "=== 6. Post 99 (apostyl na dyplom) advantages ===\n";
set_meta_if_changed(99, 'apostyl-na-dyplom-abo-atestat', 'advantages_lead',
    'Юристи перевіряють документ, готують пакет і консультують щодо подання до потрібного державного органу.');
set_meta_if_changed(99, 'apostyl-na-dyplom-abo-atestat', 'advantages_docs', [
    ['item' => 'Оригінал диплома/атестата'],
    ['item' => 'Паспорт громадянина України'],
]);

echo "=== 7. Post 146 (apostyl na dokumenty DRATS) duration 5 -> 1-2 робочих днів ===\n";
sync_post_fields(146, ['5 робочих днів' => '1-2 робочих днів'], 'apostyl-na-dokumenty-drats');

echo "=== 8. Post 147 (podviynyy apostyl) duration 10 -> 5 робочих днів ===\n";
sync_post_fields(147, ['10 робочих днів' => '5 робочих днів'], 'podviynyy-apostyl');

echo "=== 9. Post 102 (shlyub z inozemtsem) advantages_docs — add ІПН+прописка ===\n";
set_meta_if_changed(102, 'shlyub-z-inozemtsem-v-ukrayini', 'advantages_docs', [
    ['item' => "Паспорти обох з майбутнього подружжя"],
    ['item' => "Документи іноземного громадянина, перекладені й легалізовані відповідно до вимог"],
    ['item' => "Довідка про сімейний стан (за потреби — залежно від країни)"],
    ['item' => "ІПН та довідка про прописку (реєстрацію місця проживання) обох з майбутнього подружжя"],
]);

echo "=== 10. Post 120 testimonials — KOVAL -> KOVAL Legal Group ===\n";
sync_post_fields(120, ['З KOVAL все' => 'З KOVAL Legal Group все'], 'nakaz-pro-styagnennya-alimentiv');
// belt-and-suspenders explicit set in case the substring above doesn't match exactly:
set_meta_if_changed(120, 'nakaz-pro-styagnennya-alimentiv', 'testimonials', [
    [
        'text' => 'Довго не могла добитись аліментів самостійно. З KOVAL Legal Group все оформили за місяць через суд — просто передавала документи й чекала результат.',
        'cite' => 'Наталія Р., Львів',
        'is_placeholder' => true,
    ],
    [
        'text' => 'Батько дитини перестав платити добровільно, справа була неспірна.',
        'cite' => 'Руслан І., Суми',
        'is_placeholder' => true,
    ],
    [
        'text' => 'Не вірила, що можна обійтись без довгого судового процесу. Юристи порахували суму, підготували все, я тільки чекала.',
        'cite' => 'Інна Ж., Рівне',
        'is_placeholder' => true,
    ],
]);

echo "=== 11. Post 58 recategorize -> Легалізація та апостиль ===\n";
$post58 = get_post(58);
if (!$post58) {
    echo "WARN: post 58 not found — SKIPPED\n";
} else {
    $terms = wp_get_post_terms(58, 'category', ['fields' => 'ids']);
    if ($terms === [14]) {
        echo "OK (already correct): post 58 category\n";
    } else {
        $r = wp_set_post_terms(58, [14], 'category', false);
        echo is_wp_error($r) ? ("ERROR: " . $r->get_error_message() . "\n") : "Set post 58 category -> term 14\n";
    }
}

echo "=== DONE ===\n";
