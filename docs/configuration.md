Configuration Reference
=======================

Here you will find all configuration options of `sylius_grid`.

<details open><summary>Yaml</summary>

```yaml
sylius_grid:
    grids:
        app_user: # Your grid name
            driver:
                name: doctrine/orm
                options:
                    class: "%app.model.user.class%"
                    repository:
                        method: myCustomMethod
                        arguments:
                            id: resource.id
            sorting:
                name: asc
            limits: [10, 25, 50, 100]
            fields:
                name:
                    type: twig # Type of field
                    label: Name # Label
                    path: . # dot means a whole object
                    sortable: ~ | field path
                    position: 100
                    options:
                        template: "@Grid/Column/_name.html.twig" # Only twig column
                        vars:
                            labels: # a template of how does the label look like
                    enabled: true
            filters:
                name:
                    type: string # Type of filter
                    label: app.ui.name
                    enabled: true
                    template: ~
                    position: 100
                    options:
                        fields: { }
                    form_options:
                        type: contains # type of string filtering option, if you one to have just one
                    default_value:
                        type: contains
                        value: example
                enabled:
                    type: boolean # Type of filter
                    label: app.ui.enabled
                    enabled: true
                    template: ~
                    position: 100
                    options:
                        field: enabled
                    form_options: { }
                    default_value: 'false'
                date:
                    type: date # Type of filter
                    label: app.ui.created_at
                    enabled: true
                    template: ~
                    position: 100
                    options:
                        field: createdAt
                    form_options: { }
                    default_value: ~
                channel:
                    type: entity # Type of filter
                    label: app.ui.channel
                    enabled: true
                    template: ~
                    position: 100
                    options:
                        fields: [channel]
                    form_options:
                        class: "%app.model.channel.class%"
                    default_value: ~
            actions:
                main:
                    create:
                        type: create
                        label: sylius.ui.create
                        enabled: true
                        icon: ~
                        position: 100
                item:
                    update:
                        type: update
                        label: sylius.ui.edit
                        enabled: true
                        icon: ~
                        position: 100
                        options: { }
                    delete:
                        type: delete
                        label: sylius.ui.delete
                        enabled: true
                        icon: ~
                        position: 100
                        options: { }
                    show:
                        type: show
                        label: sylius.ui.show
                        enabled: true
                        icon: ~
                        position: 100
                        options:
                            link:
                                route: app_user_show
                                parameters:
                                    id: resource.id
                    archive:
                        type: archive
                        label: sylius.ui.archive
                        enabled: true
                        icon: ~
                        position: 100
                        options:
                            restore_label: sylius.ui.restore
                bulk:
                    delete:
                        type: delete
                        label: sylius.ui.delete
                        enabled: true
                        icon: ~
                        position: 100
                        options: { }
                subitem:
                    addresses:
                        type: links
                        label: sylius.ui.manage_addresses
                        options:
                            icon: cubes
                            links:
                                index:
                                    label: sylius.ui.list_addresses
                                    icon: list
                                    route: app_admin_user_address_index
                                    visible: resource.hasAddress
                                    parameters:
                                        userId: resource.id
                                create:
                                    label: sylius.ui.generate
                                    icon: random
                                    route: app_admin_user_address_create
                                    parameters:
                                        userId: resource.id
```

</details>

<details open><summary>PHP</summary>

```php
<?php

use Sylius\Bundle\GridBundle\Builder\Action\Action;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\BulkActionGroup;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\ItemActionGroup;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\MainActionGroup;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\SubItemActionGroup;
use Sylius\Bundle\GridBundle\Builder\Field\Field;
use Sylius\Bundle\GridBundle\Builder\Filter\Filter;
use Sylius\Bundle\GridBundle\Builder\GridBuilder;
use Sylius\Bundle\GridBundle\Config\GridConfig;

return static function (GridConfig $grid): void {
    $grid->addGrid(GridBuilder::create('app_user', '%app.model.user.class%')
        ->setDriver('doctrine/orm')
        ->setRepositoryMethod('myCustomMethod', ['id' => 'resource.id'])
        ->orderBy('name', 'asc')
        ->setLimits([10, 25, 50, 100])
        ->addField(
            Field::create('name', 'twig') // Name & Type of field
                ->setLabel('Name') // # Label
                ->setPath('.') // dot means a whole object
                ->setSortable(true)
                ->setPosition(100)
                ->setOptions([
                    'template' => '@Grid/Column/_name.html.twig', // Only twig column
                ])
                ->setEnabled(true)
        )
        ->addFilter(
            Filter::create('name', 'string') // Name & Type of filter
                ->setLabel('app.ui.name')
                ->setEnabled(true)
                ->setOptions(['fields' => []])
                ->setFormOptions(['type' => 'contains']) // type of string filtering option, if you one to have just one
        )
        ->addActionGroup(MainActionGroup::create(
            Action::create('create', 'create')
                ->setLabel('sylius.ui.create')
                ->setEnabled(true)
                ->setIcon('plus')
                ->setPosition(100)
                ->setOptions([]),
        ))
        ->addActionGroup(
            ItemActionGroup::create(
                Action::create('update', 'update')
                    ->setLabel('sylius.ui.edit')
                    ->setEnabled(true)
                    ->setIcon('pencil')
                    ->setPosition(100)
                    ->setOptions([]),
                Action::create('delete', 'delete')
                    ->setLabel('sylius.ui.delete')
                    ->setEnabled(true)
                    ->setIcon('trash')
                    ->setPosition(100)
                    ->setOptions([]),
                Action::create('show', 'show')
                    ->setLabel('sylius.ui.show')
                    ->setEnabled(true)
                    ->setIcon('search')
                    ->setPosition(100)
                    ->setOptions([
                        'link' => [
                            'route' => 'app_user_show',
                            'parameters' => [
                                'id' => 'resource.id',
                            ],          
                        ],          
                    ]),
                Action::create('archive', 'archive')
                    ->setLabel('sylius.ui.archive')
                    ->setEnabled(true)
                    ->setIcon('search')
                    ->setPosition(100)
                    ->setOptions([
                        'restore_label' => 'sylius.ui.restore',          
                    ]),
            )
        )
        ->addActionGroup(
            BulkActionGroup::create(
                Action::create('delete', 'delete')
                    ->setLabel('sylius.ui.delete')
                    ->setEnabled(true)
                    ->setIcon('trash')
                    ->setPosition(100)
                    ->setOptions([]),
            )
        )
        ->addActionGroup(
            SubItemActionGroup::create(
                Action::create('addresses', 'links')
                    ->setLabel('sylius.ui.manage_addresses')
                    ->setOptions([
                        'icon' => 'cubes',
                        'links' => [
                            'index' => [
                                'label' => 'sylius.ui.list_addresses',
                                'icon' => 'list',
                                'route' => 'app_admin_user_address_index',
                                'visible' => 'resource.hasAddress',
                                'parameters' => [
                                    'userId' => 'resource.id',
                                ],
                            ],
                            'create' => [
                                'label' => 'sylius.ui.generate',
                                'icon' => 'random',
                                'route' => 'app_admin_user_address_create',
                                'parameters' => [
                                    'userId' => 'resource.id',
                                ],
                            ],
                        ],
                    ]),
            )
        )
    );
};
```

</details>
