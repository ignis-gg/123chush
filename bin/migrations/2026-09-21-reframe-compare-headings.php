<?php
/**
 * Sitewide follow-up to the nesudymist compare-block reframe: every "rich"
 * service page uses the same compare_heading template ("[X] можна
 * оформити й самостійно" / "Це можна оформити й самостійно"), which
 * literally tells the visitor self-service is a valid option — works
 * against the sale. compare_lead and compare_rows on these pages are
 * already framed as real risk/benefit pairs (not DIY-validating), so only
 * the heading needs replacing, tailored per page. Two content bugs fixed
 * along the way (found while reading every page's current content):
 *   - 144: koval_text claimed KOVAL obtains a court decision copy "за
 *     довіреністю без вашої присутності" (by proxy, without the client
 *     present) — contradicts the confirmed consultation-only business
 *     model (docs/wording-audit-consultation-model.md).
 *   - 138: compare_rows was stored as a stringified var_export() dump
 *     instead of a real repeater array — rewritten as a proper array with
 *     the same (already good) content.
 */

$headings = array(
	99  => 'Де найчастіше відмовляють у апостилюванні диплома',
	101 => 'Чому легалізацію диплома краще не робити навмання',
	102 => 'Що найчастіше затримує реєстрацію шлюбу з іноземцем',
	111 => 'Чому апостиль повертають на доопрацювання',
	112 => 'Де губляться клієнти на шляху консульської легалізації',
	116 => 'Чому пакет для WES Canada часто повертають',
	119 => 'Що варто знати перед поданням заяви на розлучення',
	120 => 'Чому заяву на аліменти часто повертають без розгляду',
	137 => 'Що потрібно знати перед поданням заяви на реєстрацію шлюбу',
	138 => 'Чому типовий шаблон шлюбного договору — це ризик',
	140 => 'Чому відновлення диплома затягується на місяці',
	141 => 'Чому довідку про факт навчання часто доводиться переоформлювати',
	142 => 'Чому запит до іноземного закладу освіти часто губиться',
	143 => 'Чому суди повертають позовні заяви на доопрацювання',
	144 => 'Чому пошук судового рішення в архіві затягується',
	145 => 'Чому звичайний запит установи просто ігнорують',
	146 => 'Чому апостиль на свідоцтво ДРАЦС часто затримується',
	147 => 'Чому подвійний апостиль легко наплутати',
	148 => 'Що робити, якщо виїзд уже близько, а документ не готовий',
	150 => 'Чому МОН повертає освітні документи на доопрацювання',
	151 => 'Чому легко помилитися з органом апостилювання',
	152 => 'Чому апостилювати документ за кордоном самостійно складно',
	153 => 'Чому апостиль і легалізацію легко переплутати',
	154 => 'Чому консульська легалізація забирає найбільше часу',
	155 => 'Чому легалізація за кордоном вимагає знання місцевих процедур',
	156 => 'Чому документ можуть повернути на етапі МЗС',
	158 => 'Чому довіреність без легалізації можуть не прийняти за кордоном',
	160 => 'Чому апостильований варіант іноді не приймають',
	161 => 'Чому підтвердження розлучення критичне для нового шлюбу за кордоном',
	164 => 'Чому українські установи відмовляють у прийомі іноземного документа',
	361 => 'Чому клієнти звертаються саме до нас',
);

$updated = array();
foreach ( $headings as $id => $heading ) {
	if ( ! get_post( $id ) ) {
		echo "SKIP (not found): {$id}\n";
		continue;
	}
	update_field( 'compare_heading', $heading, $id );
	$updated[] = $id;
}

// 144 — fix the by-proxy claim.
update_field( 'compare_rows', array(
	array(
		'self_text'  => 'Незрозуміло, в якому суді шукати справу давніх років',
		'koval_text' => 'Допомагаємо визначити суд і архів за наявними даними',
	),
	array(
		'self_text'  => 'Особистий візит до суду',
		'koval_text' => "Роз'яснюємо, як звернутися за копією без зайвих візитів",
	),
	array(
		'self_text'  => 'Ризик відмови через неточні дані запиту',
		'koval_text' => 'Консультуємо, як підготувати запит коректно',
	),
), 144 );

// 138 — rebuild as a proper repeater array (was a stringified dump).
update_field( 'compare_rows', array(
	array(
		'self_text'  => 'Типовий шаблон з інтернету не враховує вашу ситуацію',
		'koval_text' => 'Консультуємо, як підготувати індивідуальні умови під вашу ситуацію',
	),
	array(
		'self_text'  => 'Ризик оскарження договору через юридичні неточності',
		'koval_text' => 'Формулюємо пункти юридично коректно',
	),
	array(
		'self_text'  => 'Незрозуміло, що взагалі можна включити в договір',
		'koval_text' => 'Консультуємо щодо меж, дозволених законодавством',
	),
), 138 );

echo 'UPDATED ' . count( $updated ) . " headings: " . implode( ',', $updated ) . "\n";
echo "FIXED: 144 (by-proxy wording), 138 (malformed compare_rows)\n";
