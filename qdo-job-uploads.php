<?php
/*
Plugin Name: QDO Uploads
Description: Formulários de Upload de Arquivos
Version: 1.0
Author: QuodDG
*/

require_once plugin_dir_path(__FILE__) . 'includes/qdo-functions.php';

register_activation_hook( __FILE__, 'qdo_jobs_chk_version' );