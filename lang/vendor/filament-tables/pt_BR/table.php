<?php

return [
    'column_toggle' => [
        'heading' => 'Colunas',
    ],
    'columns' => [
        'text' => [
            'more_list_items' => 'e mais :count',
        ],
    ],
    'fields' => [
        'bulk_select_page' => [
            'label' => 'Selecionar/deselecionar todos os itens para ações em massa.',
        ],
        'bulk_select_record' => [
            'label' => 'Selecionar/deselecionar item :key para ações em massa.',
        ],
        'bulk_select_group' => [
            'label' => 'Selecionar/deselecionar grupo :title para ações em massa.',
        ],
        'search' => [
            'label' => 'Pesquisar',
            'placeholder' => 'Pesquisar',
            'indicator' => 'Pesquisar',
        ],
    ],
    'summary' => [
        'heading' => 'Resumo',
        'subheadings' => [
            'all' => 'Todos :label',
            'group' => ':group resumo',
            'page' => 'Esta página',
        ],
        'summarizers' => [
            'average' => [
                'label' => 'Média',
            ],
            'count' => [
                'label' => 'Contagem',
            ],
            'sum' => [
                'label' => 'Soma',
            ],
        ],
    ],
    'actions' => [
        'disable_reordering' => [
            'label' => 'Terminar de reordenar registros',
        ],
        'enable_reordering' => [
            'label' => 'Reordenar registros',
        ],
        'filter' => [
            'label' => 'Filtrar',
        ],
        'group' => [
            'label' => 'Agrupar',
        ],
        'open_bulk_actions' => [
            'label' => 'Ações em massa',
        ],
        'toggle_columns' => [
            'label' => 'Alternar colunas',
        ],
    ],
    'empty' => [
        'heading' => 'Nenhum registro encontrado',
        'description' => 'Crie um :model para começar.',
    ],
    'filters' => [
        'actions' => [
            'apply' => [
                'label' => 'Aplicar filtros',
            ],
            'remove' => [
                'label' => 'Remover filtro',
            ],
            'remove_all' => [
                'label' => 'Remover todos os filtros',
                'tooltip' => 'Remover todos os filtros',
            ],
            'reset' => [
                'label' => 'Redefinir',
            ],
        ],
        'heading' => 'Filtros',
        'indicator' => 'Filtros ativos',
        'multi_select' => [
            'placeholder' => 'Todos',
        ],
        'select' => [
            'placeholder' => 'Todos',
        ],
        'trashed' => [
            'label' => 'Registros excluídos',
            'only_trashed' => 'Apenas registros excluídos',
            'with_trashed' => 'Com registros excluídos',
            'without_trashed' => 'Sem registros excluídos',
        ],
    ],
    'grouping' => [
        'fields' => [
            'group' => [
                'label' => 'Agrupar por',
                'placeholder' => 'Agrupar por',
            ],
            'direction' => [
                'label' => 'Direção do agrupamento',
                'options' => [
                    'asc' => 'Crescente',
                    'desc' => 'Decrescente',
                ],
            ],
        ],
    ],
    'reorder_indicator' => 'Arraste e solte os registros na ordem.',
    'selection_indicator' => [
        'selected_count' => '1 registro selecionado|:count registros selecionados',
        'actions' => [
            'select_all' => [
                'label' => 'Selecionar todos :count',
            ],
            'deselect_all' => [
                'label' => 'Desmarcar todos',
            ],
        ],
    ],
    'sorting' => [
        'fields' => [
            'column' => [
                'label' => 'Ordenar por',
            ],
            'direction' => [
                'label' => 'Direção da ordenação',
                'options' => [
                    'asc' => 'Crescente',
                    'desc' => 'Decrescente',
                ],
            ],
        ],
    ],
];
