Custom Filter
=============

Sylius Grids come with built-in filters, but there are use-cases where you need something more than basic filter. Grids allow you to define your own filter types!

To add a new filter, we need to create an appropriate class and form type.

```php
<?php

namespace App\Grid\Filter;

use App\Form\Type\Filter\SuppliersStatisticsFilterType;
use Sylius\Bundle\GridBundle\Doctrine\DataSourceInterface;
use Sylius\Component\Grid\Filtering\ConfigurableFilterInterface;

class SuppliersStatisticsFilter implements ConfigurableFilterInterface
{
    public function apply(DataSourceInterface $dataSource, $name, $data, array $options = []): void
    {
        // Your filtering logic.
        // $data['stats'] contains the submitted value!
        // here is an example
        $queryBuilder = $dataSource->getQueryBuilder();
        $queryBuilder
            ->andWhere('stats = :stats')
            ->setParameter(':stats', $data['stats'])
        ;
    
        // For driver abstraction you can use the expression builder. ExpressionBuilder is kind of query builder.
        // $data['stats'] contains the submitted value!
        // here is an example
        $dataSource->restrict($dataSource->getExpressionBuilder()->equals('stats', $data['stats']));
    }
    
    public static function getType() : string
    {
        return 'suppliers_statistics';
    }
    
    public static function getFormType() : string
    {
        return SuppliersStatisticsFilterType::class;
    }
}
```

And the form type:

```php
<?php

namespace App\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuppliersStatisticsFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'stats',
            ChoiceType::class,
            ['choices' => range($options['range'][0], $options['range'][1])]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'range' => [0, 10],
            ])
            ->setAllowedTypes('range', ['array'])
        ;
    }
}
```

Create a template for the filter, similar to the existing ones:

```html
# templates/Grid/Filter/suppliers_statistics.html.twig
{% form_theme form '@SyliusUi/Form/theme.html.twig' %}

{{ form_row(form) }}
```


If you use Autoconfiguration, the filter is automatically registered as a grid filter.

But if you don't use autoconfiguration, let's register your new filter type as service.

```yaml
# config/services.yaml

services:
    App\Grid\Filter\SuppliersStatisticsFilter:
        tags: ['sylius.grid_filter']
```

Now you can use your new filter type in the grid configuration!

<details open><summary>Yaml</summary>

```yaml
# config/packages/sylius_grid.yaml

sylius_grid:
    grids:
        app_tournament:
            driver: doctrine/orm
            resource: app.tournament
            filters:
                stats:
                    type: suppliers_statistics
                    form_options:
                        range: [0, 100]
    templates:
        filter:
            suppliers_statistics: '@App/Grid/Filter/suppliers_statistics.html.twig'
```

</details>

<details open><summary>PHP</summary>

```php
<?php
// config/packages/sylius_grid.php

use App\Entity\Tournament;
use Sylius\Bundle\GridBundle\Builder\Action\Action;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\ItemActionGroup;
use Sylius\Bundle\GridBundle\Builder\GridBuilder;
use Sylius\Bundle\GridBundle\Builder\Filter\Filter;
use Sylius\Bundle\GridBundle\Config\GridConfig;

return static function (GridConfig $grid) {
    $grid->addGrid(GridBuilder::create('app_tournament', Tournament::class)
        ->addFilter(
            Filter::create('stats', 'suppliers_statistics')
                ->setFormOptions(['range' => [0, 100]])
        )
    )
};
```

OR

```php
<?php
# src/Grid/TournamentGrid.php

declare(strict_types=1);

namespace App\Grid;

use App\Entity\Tournament;
use Sylius\Bundle\GridBundle\Builder\GridBuilderInterface;
use Sylius\Bundle\GridBundle\Grid\AbstractGrid;
use Sylius\Bundle\GridBundle\Grid\ResourceAwareGridInterface;

final class TournamentGrid extends AbstractGrid implements ResourceAwareGridInterface
{
    public static function getName(): string
    {
           return 'app_tournament';
    }

    public function buildGrid(GridBuilderInterface $gridBuilder): void
    {
        $gridBuilder
            ->addFilter(
                Filter::create('stats', 'suppliers_statistics')
                    ->setFormOptions(['range' => [0, 100]])
            )
        ;    
    }
    
    public function getResourceClass(): string
    {
        return Tournament::class;
    }
}
```

</details>
