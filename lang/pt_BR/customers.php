<?php

return [

    'title' => 'Clientes',
    'trash_title' => 'Lixeira',
    'create_title' => 'Novo Cliente',
    'edit_title' => 'Editar Cliente',
    'show_title' => ':name',

    'create' => 'Criar Cliente',
    'back' => 'Voltar',
    'search' => 'Buscar',
    'search_placeholder' => 'Buscar por nome, e-mail, telefone ou conta bancária…',
    'search_hint' => 'Pressione Enter para buscar',
    'trash' => 'Lixeira',
    'trash_banner' => 'Você está na lixeira. Os clientes aqui podem ser restaurados ou excluídos permanentemente.',
    'dismiss' => 'Fechar',

    'sort' => [
        'newest' => 'Mais recentes',
        'oldest' => 'Mais antigos',
    ],

    'columns' => [
        'customer' => 'Cliente',
        'actions' => 'Ações',
        'created' => 'Cadastro',
    ],

    'fields' => [
        'first_name' => 'Nome',
        'last_name' => 'Sobrenome',
        'phone' => 'Telefone',
        'email' => 'E-mail',
        'ban' => 'Conta Bancária',
        'about' => 'Sobre',
        'image' => 'Imagem',
        'image_optional' => 'Imagem (opcional)',
    ],

    'buttons' => [
        'create' => 'Criar',
        'update' => 'Salvar',
        'saving' => 'Salvando…',
        'edit' => 'Editar',
        'delete' => 'Excluir',
        'cancel' => 'Cancelar',
        'confirm' => 'Confirmar',
        'restore' => 'Restaurar',
        'force_delete' => 'Excluir permanentemente',
        'restore_selected' => 'Restaurar selecionados',
        'force_delete_selected' => 'Excluir selecionados permanentemente',
    ],

    'actions' => [
        'edit' => 'Editar cliente',
        'view' => 'Ver cliente',
        'delete' => 'Excluir cliente',
        'restore' => 'Restaurar cliente',
        'force_delete' => 'Excluir permanentemente',
    ],

    'confirm' => [
        'delete' => 'Excluir este cliente?',
        'delete_description' => 'O cliente será movido para a lixeira e poderá ser restaurado depois.',
        'restore' => 'Restaurar este cliente?',
        'restore_description' => 'O cliente voltará para a lista principal.',
        'force_delete' => 'Excluir permanentemente este cliente?',
        'force_delete_description' => 'Esta ação não pode ser desfeita. Todos os dados serão removidos.',
        'bulk_restore' => 'Restaurar :count cliente(s) selecionado(s)?',
        'bulk_force_delete' => 'Excluir permanentemente :count cliente(s)? Esta ação não pode ser desfeita.',
    ],

    'empty' => [
        'none' => 'Nenhum cliente cadastrado.',
        'none_description' => 'Comece adicionando seu primeiro cliente.',
        'none_search' => 'Nenhum cliente encontrado para ":search".',
        'trash_none' => 'Nenhum cliente na lixeira.',
        'trash_none_description' => 'Clientes excluídos aparecerão aqui.',
        'create_cta' => 'Criar primeiro cliente',
        'clear_search' => 'Limpar busca',
    ],

    'flash' => [
        'created' => 'Cliente criado com sucesso.',
        'updated' => 'Cliente atualizado com sucesso.',
        'deleted' => 'Cliente excluído com sucesso.',
        'restored' => 'Cliente restaurado com sucesso.',
        'force_deleted' => 'Cliente excluído permanentemente.',
        'restore_failed' => 'Não foi possível restaurar o cliente. Ele pode já ter sido restaurado.',
        'restore_email_conflict' => 'Não foi possível restaurar: já existe um cliente ativo com esse e-mail.',
        'bulk_restored' => ':count cliente(s) restaurado(s) com sucesso.',
        'bulk_force_deleted' => ':count cliente(s) excluído(s) permanentemente.',
        'bulk_none_selected' => 'Nenhum cliente selecionado.',
        'bulk_none_matched' => 'Nenhum cliente selecionado encontrado na lixeira.',
    ],

    'image' => [
        'current' => 'Imagem atual. Envie um novo arquivo para substituí-la.',
        'current_alt' => 'Imagem atual de :name',
        'preview_alt' => 'Pré-visualização da imagem',
    ],

    'not_informed' => 'Não informado',

    'history' => [
        'title' => 'Histórico',
        'created' => 'Criado em',
        'updated' => 'Atualizado em',
        'deleted' => 'Excluído em',
    ],

    'command_palette' => [
        'title' => 'Buscar clientes',
        'placeholder' => 'Digite para buscar clientes…',
        'hint' => 'Use ↑↓ para navegar, Enter para abrir, Esc para fechar',
        'no_results' => 'Nenhum cliente encontrado.',
        'shortcut' => '⌘K',
    ],

    'bulk' => [
        'selected' => ':count selecionado(s)',
        'select_all' => 'Selecionar todos',
    ],

    'breadcrumbs' => [
        'customers' => 'Clientes',
        'trash' => 'Lixeira',
        'create' => 'Novo',
        'edit' => 'Editar',
    ],

    'errors' => [
        'summary_title' => 'Corrija os erros abaixo:',
    ],

];
