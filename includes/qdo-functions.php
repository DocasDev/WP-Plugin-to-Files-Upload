<?php

define('TESTE', false);
define('QDO_JOB_DB_VERSION', 'qdo_job_db_version');
define('QDO_JOB_PREFIX_ID', '$hd54j-s7j31');
define('QDO_JOB_PREFIX_INPUT_NAME', 'qdo-jobs-');
define('QDO_JOB_PREFIX_INPUT_FILE_NAME', 'qdo-jobs-file-');

global $qdo_job_version;
global $wp_actions;
$qdo_job_version = 1.0;

// active custom types
function qdo_jobs_active_custom_types()
{
    $qdo_jobs_wp_declared_classes = get_declared_classes();
    foreach (glob(dirname(__FILE__) . "/post-types/qdo-custom-type-*.php") as $filename) {
        require $filename;
    }

    $qdo_jobs_custom_types_classes = array_values(array_diff_key(get_declared_classes(), $qdo_jobs_wp_declared_classes));
    foreach ($qdo_jobs_custom_types_classes as $class_name) {
        new $class_name();
    }
}

add_action('init', 'qdo_jobs_active_custom_types');
add_action('admin_menu', 'qdo_jobs_admin_setup');

function qdo_jobs_admin_setup()
{
    add_menu_page('Formulários de Upload', 'QDO Formulários', 'manage_options', 'edit.php?post_type=formularios', '', 'dashicons-feedback');
    //add_submenu_page('qdo-jobs-acp-page', 'Form. Eventos', 'Converter para Eventos', 'manage_options', 'edit.php?post_type=formularios', '');
    remove_menu_page('edit.php?post_type=formularios');
}

function qdo_jobs_acp_page()
{
    require_once __DIR__ . '/qdo-jobs-acp-page.php';
}

add_filter('manage_edit-formularios_columns', 'qdo_jobs_formulario_columns');

function qdo_jobs_formulario_columns($columns)
{
    $columns['formularios_shortcode'] = 'Shortcode';
    return $columns;
}

add_action('manage_posts_custom_column', 'qdo_populate_columns');

function qdo_populate_columns($column)
{
    if ('formularios_shortcode' === $column) {
        echo '[formularios=' . get_the_ID() . ']';
    }
}

add_shortcode('formularios', 'qdo_jobs_display_custom_post_type');

function qdo_jobs_display_custom_post_type($form_id)
{
    if (is_admin()) {
        return;
    }
    $string = '';
    $query = qdo_jobs_get_form_post($form_id);

    while ($query->have_posts()) {
        $query->the_post();
        //ob_start();
        //require_once dirname(__FILE__) . "/parts/qdo-formulario.php";
        $string = qdo_jobs_render_template('qdo-formulario');
    }
    wp_reset_postdata();
    return $string;
}

function qdo_jobs_get_form_post($form_id)
{
    $args = array(
        'post_type' => 'formularios',
        'post_status' => 'publish',
        'post_ID' => intval($form_id),
        'posts_per_page' => 1
    );

    return $query = new WP_Query($args);
}

add_filter('qdo_jobs_send_form', 'qdo_jobs_send_form');

function qdo_jobs_send_form()
{
    if (!count($_POST)) {
        return false;
    }

    $response = [
        'class' => 'success',
        'msg' => 'Os documentos foram enviados. Aguarde o retorno.',
        'status' => true
    ];

    try {
        $g_recaptcha = qdo_jobs_chkInput(INPUT_POST, 'g-recaptcha-response', true);
        if (!TESTE && ( $g_recaptcha === false || !qdo_jobs_verify_captcha($g_recaptcha) )) {
            throw new Exception('Captcha não verificado.');
        }

        $form_id = qdo_jobs_chkInput(INPUT_POST, 'qdo-jobs', true);
        if ($form_id === false) {
            throw new Exception('Formulário Inválido.');
        }
        $form_id = intval(preg_replace('/' . QDO_JOB_PREFIX_ID . '/', '', $form_id));
        $form_data = [
            'info' => qdo_jobs_get_sended_fields($form_id),
            'files' => qdo_jobs_get_sended_files($form_id)
        ];

        $to = explode("\n", get_field('emails'));
        qdo_jobs_send_mail($form_id, $to, $form_data);
    } catch (Exception $ex) {
        $response['class'] = 'danger';
        $response['msg'] = $ex->getMessage();
        $response['status'] = false;
    }

    return $response;
}

function qdo_jobs_send_mail($form_id, $to, $form_data)
{
    qdo_jobs_verify_to($to);

    $form_title = 'Formulário - ' . get_post($form_id)->post_title;
    $message = qdo_jobs_render_template('qdo-send-mail', [
        'title' => $form_title,
        'info' => $form_data['info'],
        'observacao' => get_field('observacoes', $form_id)
    ]);
    $from_name = $form_data['info'][QDO_JOB_PREFIX_INPUT_NAME . 'nome'];
    $from_email = $form_data['info'][QDO_JOB_PREFIX_INPUT_NAME . 'e-mail'];

    $headers = "From: {$from_name} <{$from_email}>\r\n";
    $headers .= "Reply-To: " . $from_email . "\r\n";
    $headers .= "Cc: " . join(', ', array_slice($to, 1, count($to) - 1)) . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html; charset=utf-8\n";

    $attachments = $form_data['files'];
    if (!wp_mail($to[0], $form_title, $message, $headers, $attachments)) {
        throw new Exception('Não foi possível enviar o formulário. Tente novamente ou entre entre em <a href="/atendimento">contato</a> conosco.');
    }
}

function qdo_jobs_verify_to($to)
{
    if (!is_array($to) || !count($to)) {
        throw new Exception('Formulário Inválido.');
    }

    foreach ($to as $email) {
        if (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Erro geral. Pelo menos um destinatário não é um e-mail válido. Entre entre em <a href="/atendimento">contato</a> conosco.');
        }
    }
}

function qdo_jobs_get_sended_fields($form_id)
{
    $fields = [];
    //$form_id = intval($form_id);
    $info_fields = get_field('informacoes_necessarias', $form_id);
    foreach ($info_fields as $info) {
        $info_field = explode(':', $info);
        qdo_jobs_verify_sended_fields(qdo_jobs_sanitize_text($info_field[1]), $fields);
    }

    return $fields;
}

function qdo_jobs_verify_sended_fields($sended, array &$fields)
{
    $name = QDO_JOB_PREFIX_INPUT_NAME . $sended;
    $field = qdo_jobs_chkInput(INPUT_POST, $name, true);

    if ($field === false) {
        throw new Exception('Inconsistência nos dados.');
    }

    if ($sended === 'cpf' && !qdo_jobs_validar_cpf($field)) {
        throw new Exception('CPF Inválido.');
    }

    if ($sended === 'e-mail' && !filter_var($field, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('E-mail inválido.');
    }

    $fields[$name] = $field;
}

function qdo_jobs_get_sended_files($form_id)
{
    $files = [];
    //$form_id = intval($form_id);
    $max_files = intval(get_field('maximo_de_arquivos', $form_id));
    for ($i = 1; $i <= $max_files; $i++) {
        qdo_jobs_verify_sended_files($i, $form_id, $files);
    }

    if (!count($files)) {
        throw new Exception('Nenhum arquivo foi identificado.');
    }

    return $files;
}

function qdo_jobs_verify_sended_files($file_number, $form_id, array &$files)
{
    $file = qdo_jobs_chkInputFile(QDO_JOB_PREFIX_INPUT_FILE_NAME . $file_number);
    if (is_null($file)) {
        return;
    }

    $max_file_size = intval(get_field('tamanho_maximo_de_arquivos', $form_id));
    if ($file['size'] > ($max_file_size * 1048576)) {
        throw new Exception('O arquivo <strong>' . $file['name'] . '</strong> é muito grande. Tamanho máximo permitido de <strong>' . $max_file_size . 'Mb</strong>.');
    }

    $allowed_file_types = get_field('arquivos_aceitos', $form_id);
    $type = $file['type'];
    $allowed = false;
    foreach (qdo_jobs_get_allowed_type_values($allowed_file_types) as $mime) {
        if (strstr($mime, $type) !== false) {
            $allowed = true;
            break;
        }
    }

    if (!$allowed) {
        throw new Exception('O arquivo <strong>' . $file['name'] . '</strong> é de um tipo não permitido. Tipos permitidos: <strong>' . join(', ', qdo_jobs_get_allowed_type_labels($allowed_file_types)) . '</strong>.');
    }

    $attachment = wp_handle_upload($file, ['test_form' => false]);
    if (array_key_exists('error', $attachment)) {
        qdo_jobs_log('Erro na tentativa de upload do arquivo "' . $file['name'] . '" >> ' . $attachment['error']);
        throw new Exception('O arquivo <strong>' . $file['name'] . '</strong> não pôde ser enviado. Tente novamente mais tarde ou entre em <a href="/atendimento">contato</a> conosco.');
    }

    $files[] = $attachment['file'];
}

function qdo_jobs_get_allowed_type_values($allowed_file_types)
{
    if (!is_array($allowed_file_types)) {
        return [];
    }

    $values = [];
    foreach ($allowed_file_types as $t) {
        $values[] = $t['value'];
    }

    return $values;
}

function qdo_jobs_get_allowed_type_labels($allowed_file_types)
{
    if (!is_array($allowed_file_types)) {
        return [];
    }

    $labels = [];
    foreach ($allowed_file_types as $t) {
        $labels[] = $t['label'];
    }

    return $labels;
}

function qdo_jobs_verify_captcha($g_recaptcha)
{
    if (is_null($g_recaptcha) || $g_recaptcha === false || empty($g_recaptcha)) {
        return false;
    }

    $url_check_g_recaptcha = 'https://www.google.com/recaptcha/api/siteverify';
    $s = curl_init();
    curl_setopt($s, CURLOPT_URL, $url_check_g_recaptcha);
    curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($s, CURLOPT_POST, true);
    curl_setopt($s, CURLOPT_POSTFIELDS, [
        'secret' => 'SECRET_KEY',
        'response' => $g_recaptcha
    ]);
    $g_recaptcha_response = json_decode(curl_exec($s), true);
    curl_close($s);

    if (!$g_recaptcha_response['success']) {
        return false;
    }

    return true;
}

function qdo_jobs_chk_version()
{
    global $qdo_job_version;

    if (empty(get_option(QDO_JOB_DB_VERSION))) {
        qdo_jobs_install();
    }

    qdo_jobs_chk_upgrade();
}

function qdo_jobs_install()
{
    global $wpdb;
    global $qdo_job_version;

    $charset_collate = $wpdb->get_charset_collate();
    $sql = "
    ";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);
    add_option(QDO_JOB_DB_VERSION, 1.0);
}

function qdo_jobs_chk_upgrade()
{
    global $wpdb;
    global $qdo_job_version;

    try {
        $conn = qdo_jobs_getConnection();
        $conn->beginTransaction();

        /* if (floatval(get_option(QDO_JOB_DB_VERSION)) < 1.3) {
          $sql = "";
          $stmtm = $conn->prepare($sql);
          $stmtm->execute();
          } */

        $conn->commit();
        update_option(QDO_JOB_DB_VERSION, $qdo_job_version);
    } catch (PDOException $e) {
        $conn->rollBack();
        echo $e->getMessage();
    }
}

function qdo_jobs_chkInput($input_type, $var_name, $chk_empty = false, $clear = false)
{
    if ($clear) {
        return '';
    }
    $in = filter_input($input_type, $var_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if ($chk_empty && empty($in)) {
        return false;
    }

    return !is_null($in) && $in !== false ? $in : false;
}

function qdo_jobs_chkInputFile($var_name)
{
    return isset($_FILES[$var_name]) &&
            is_array($_FILES[$var_name]) &&
            array_key_exists('error', $_FILES[$var_name]) &&
            $_FILES[$var_name]['error'] === UPLOAD_ERR_OK ?
            $_FILES[$var_name] : null;
}

/**
 * 
 * @return \PDO
 */
function qdo_jobs_getConnection()
{
    return new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASSWORD);
}

function qdo_jobs_escapeParam($param)
{
    return trim(str_replace(['\'', '"', ';', '%'], '', strip_tags($param)));
}

function qdo_jobs_parseDuoTime2Str($date1, $date2, $prefix = 'no período de ')
{
    $dt1 = new DateTime(preg_replace('/[\/]/', '-', $date1));
    $dt2 = new DateTime(preg_replace('/[\/]/', '-', $date2));

    $format1 = '%d de %B de %Y a ';
    $format2 = '%d de %B de %Y';

    if ($dt1->format('m') === $dt2->format('m') && $dt1->format('Y') === $dt2->format('Y')) {
        $format1 = '%d a ';
    } else if ($dt1->format('Y') === $dt2->format('Y')) {
        $format1 = '%d de %B a ';
    }

    return qdo_jobs_convertEncoding($prefix) . utf8_decode(strftime($format1, strtotime($date1))) . utf8_decode(strftime($format2, strtotime($date2)));
}

function qdo_jobs_validar_cpf($cpf)
{
    $cpf = preg_replace('/[^0-9]/', '', (string) $cpf);
    // Valida tamanho
    if (strlen($cpf) !== 11) {
        return false;
    }
    // Calcula e confere primeiro dígito verificador
    $soma = 0;
    for ($i = 0, $j = 10; $i < 9; $i++, $j--) {
        $soma += $cpf{$i} * $j;
    }
    $resto = $soma % 11;
    if ($cpf{9} != ($resto < 2 ? 0 : 11 - $resto)) {
        return false;
    }
    // Calcula e confere segundo dígito verificador
    for ($i = 0, $j = 11, $soma = 0; $i < 10; $i++, $j--) {
        $soma += $cpf{$i} * $j;
    }
    $resto = $soma % 11;
    return $cpf{10} == ($resto < 2 ? 0 : 11 - $resto);
}

function qdo_jobs_sanitize_text($str)
{
    return sanitize_title($str);
}

function qdo_jobs_log($content)
{
    $file = fopen(dirname(__FILE__) . '/logs/geral.txt', 'a+');
    if ($file !== false) {
        fwrite($file, date('d/m/Y H:i:s') . ' :: ' . $content);
        fclose($file);
    }
}

function qdo_jobs_render_template($template, array $args = [])
{
    $content = '';
    ob_start();
    extract($args);
    require dirname(__FILE__) . "/parts/template-{$template}.php";
    $content = ob_get_clean();

    return $content;
}
