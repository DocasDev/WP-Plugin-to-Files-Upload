<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Forms
 *
 * @author Quod
 */
class Forms
{

    private $posttype = 'formularios';
    private $name = 'Formularios';
    private $singular_name = 'Formulario';
    private $group_title = 'Formulários Upload';

    function __construct()
    {
        $this->custom_post_type_generate();
        $this->custom_field_generate();
    }

    private function custom_post_type_generate()
    {
        $labels = [
            'name' => $this->name,
            'singular_name' => $this->singular_name,
            'add_new' => 'Adicionar Novo',
            'add_new_item' => 'Adicionar Novo',
            'edit_item' => 'Editar',
            'new_item' => 'Novo',
            'view_item' => 'Ver',
            'search_items' => 'Buscar',
            'not_found' => 'Nada foi encontrado',
            'not_found_in_trash' => 'Nada foi encontrado na Lixeira',
            'parent_item_colon' => '',
            'menu_name' => $this->name
        ];

        register_post_type($this->posttype, [
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'menu_position' => 9,
            'menu_icon' => 'dashicons-feedback',
            'rewrite' => false,
            'has_archive' => false,
            'query_var' => false,
            'supports' => ['title']
        ]);

        flush_rewrite_rules(false);
    }

    private function custom_field_generate()
    {
        if (function_exists('acf_add_local_field_group')) {

            acf_add_local_field_group([
                'key' => 'group__qdo_jobs_0001',
                'title' => $this->group_title,
                'fields' => $this->get_custom_fields(),
                'location' => [
                    [
                        [
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => $this->posttype,
                        ],
                    ],
                ],
                'menu_order' => 0,
                'position' => 'normal',
                'style' => 'default',
                'label_placement' => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen' => '',
                'active' => true,
                'description' => '',
            ]);
        }
    }

    private function get_custom_fields()
    {
        return [
            [
                'key' => 'field__qdo_jobs_0001',
                'label' => 'Configurações do Formulário',
                'name' => '',
                'type' => 'tab',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'placement' => 'top',
                'endpoint' => 0,
            ],
            [
                'key' => 'field__qdo_jobs_0002',
                'label' => 'E-mails',
                'name' => 'emails',
                'type' => 'textarea',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'placeholder' => '',
                'maxlength' => '',
                'rows' => '',
                'new_lines' => '',
            ],
            [
                'key' => 'field__qdo_jobs_0003',
                'label' => 'Inicio da Submissão',
                'name' => 'inicio_da_submissao',
                'type' => 'date_time_picker',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'display_format' => 'd/m/Y H:i',
                'return_format' => 'Y-m-d H:i:s',
                'first_day' => 0,
            ],
            [
                'key' => 'field__qdo_jobs_0004',
                'label' => 'Fim da Submissão',
                'name' => 'fim_da_submissao',
                'type' => 'date_time_picker',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'display_format' => 'd/m/Y H:i',
                'return_format' => 'Y-m-d H:i:s',
                'first_day' => 0,
            ],
            [
                'key' => 'field__qdo_jobs_0005',
                'label' => 'Máximo de Arquivos',
                'name' => 'maximo_de_arquivos',
                'type' => 'number',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'min' => 1,
                'max' => '',
                'step' => '',
            ],
            [
                'key' => 'field__qdo_jobs_0006',
                'label' => 'Arquivos Aceitos',
                'name' => 'arquivos_aceitos',
                'type' => 'checkbox',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'choices' => [
                    'application/msword' => 'Word (.doc)',
                    'application/excel' => 'Excel (.xls)',
                    'application/vnd.ms-powerpoint' => 'PowerPoint (.ppt)',
                    'application/pdf' => 'PDF',
                    'application/x-zip, application/zip, application/x-zip-compressed' => 'Zip',
                    'image/gif, image/png, image/jpeg, image/bmp' => 'Imagens',
                ],
                'allow_custom' => 1,
                'save_custom' => 1,
                'default_value' => [],
                'layout' => 'vertical',
                'toggle' => 0,
                'return_format' => 'array',
            ],
            [
                'key' => 'field__qdo_jobs_0007',
                'label' => 'Tamanho Máximo do Arquivo',
                'name' => 'tamanho_maximo_de_arquivos',
                'type' => 'number',
                'instructions' => 'Tamanho máximo permitido em Megabytes (Mb)',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'min' => 1,
                'max' => 4,
                'step' => '',
            ],
            [
                'key' => 'field__qdo_jobs_0008',
                'label' => 'Observações',
                'name' => 'observacoes',
                'type' => 'wysiwyg',
                'instructions' => 'Texto enviado por e-mail juntamente com os dados, para melhor compreenção do destinatário',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'toolbar' => 'full',
                'media_upload' => 1,
                'tabs' => 'all',
                'delay' => 0,
            ],
            [
                'key' => 'field__qdo_jobs_0101',
                'label' => 'Configurações do Remetente',
                'name' => '',
                'type' => 'tab',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'placement' => 'top',
                'endpoint' => 0,
            ],
            [
                'key' => 'field__qdo_jobs_0102',
                'label' => 'Informações Necessárias',
                'name' => 'informacoes_necessarias',
                'type' => 'checkbox',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'choices' => [
                    'text:matricula' => 'Matrícula',
                    'text:Nome' => 'Nome',
                    'text:Curso' => 'Curso',
                    'email:E-mail' => 'E-mail',
                    'text:Semestre' => 'Semestre',
                    'tel:Telefone' => 'Telefone',
                    'text:CPF' => 'CPF',
                ],
                'allow_custom' => 1,
                'save_custom' => 1,
                'default_value' => [],
                'layout' => 'vertical',
                'toggle' => 0,
                'return_format' => 'value',
            ],
            [
                'key' => 'field__qdo_jobs_0201',
                'label' => 'Informações Adicionais',
                'name' => '',
                'type' => 'tab',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'placement' => 'top',
                'endpoint' => 0,
            ],
            [
                'key' => 'field__qdo_jobs_0202',
                'label' => 'Resumo',
                'name' => 'resumo',
                'type' => 'wysiwyg',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'toolbar' => 'full',
                'media_upload' => 1,
                'tabs' => 'all',
                'delay' => 0,
            ]
        ];
    }

}
