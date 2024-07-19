<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\GridBundle\Renderer;

use Sylius\Bundle\GridBundle\Form\Registry\FormTypeRegistryInterface;
use Sylius\Component\Grid\Definition\Action;
use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\Definition\Filter;
use Sylius\Component\Grid\FieldTypes\FieldTypeInterface;
use Sylius\Component\Grid\Renderer\Context\Factory\ActionContextFactory;
use Sylius\Component\Grid\Renderer\Context\Factory\ActionContextFactoryInterface;
use Sylius\Component\Grid\Renderer\Context\Factory\FilterContextFactory;
use Sylius\Component\Grid\Renderer\Context\Factory\FilterContextFactoryInterface;
use Sylius\Component\Grid\Renderer\GridRendererInterface;
use Sylius\Component\Grid\View\GridViewInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

final class TwigGridRenderer implements GridRendererInterface
{
    private ActionContextFactoryInterface $actionContextFactory;

    private FilterContextFactoryInterface $filterContextFactory;

    public function __construct(
        private Environment $twig,
        private ServiceRegistryInterface $fieldsRegistry,
        private FormFactoryInterface $formFactory,
        private FormTypeRegistryInterface $formTypeRegistry,
        private string $defaultTemplate,
        private array $actionTemplates = [],
        private array $filterTemplates = [],
        ?ActionContextFactoryInterface $actionContextFactory = null,
        ?FilterContextFactoryInterface $filterContextFactory = null,
    ) {
        if (null === $actionContextFactory) {
            trigger_deprecation(
                'sylius/grid-bundle',
                '1.13',
                sprintf(
                    'You should pass an instance of "%s" as 8th argument. It will be required in Sylius Grid 2.0.',
                    ActionContextFactoryInterface::class,
                ),
            );
        }

        if (null === $filterContextFactory) {
            trigger_deprecation(
                'sylius/grid-bundle',
                '1.13',
                sprintf(
                    'You should pass an instance of "%s" as 9th argument. It will be required in Sylius Grid 2.0.',
                    FilterContextFactoryInterface::class,
                ),
            );
        }

        $this->actionContextFactory = $actionContextFactory ?? new ActionContextFactory();
        $this->filterContextFactory = $filterContextFactory ?? new FilterContextFactory();
    }

    public function render(GridViewInterface $gridView, ?string $template = null)
    {
        return $this->twig->render($template ?: $this->defaultTemplate, ['grid' => $gridView]);
    }

    public function renderField(GridViewInterface $gridView, Field $field, $data)
    {
        /** @var FieldTypeInterface $fieldType */
        $fieldType = $this->fieldsRegistry->get($field->getType());
        $resolver = new OptionsResolver();
        $fieldType->configureOptions($resolver);
        $options = $resolver->resolve($field->getOptions());

        return $fieldType->render($field, $data, $options);
    }

    public function renderAction(GridViewInterface $gridView, Action $action, $data = null)
    {
        $type = $action->getType();
        if (!isset($this->actionTemplates[$type])) {
            throw new \InvalidArgumentException(sprintf('Missing template for action type "%s".', $type));
        }

        $context = $this->actionContextFactory->create($gridView, $action, $data);

        return $this->twig->render($this->actionTemplates[$type], $context);
    }

    public function renderFilter(GridViewInterface $gridView, Filter $filter)
    {
        $template = $this->getFilterTemplate($filter);

        $form = $this->formFactory->createNamed('criteria', FormType::class, [], [
            'allow_extra_fields' => true,
            'csrf_protection' => false,
            'required' => false,
        ]);
        $form->add(
            $filter->getName(),
            $this->formTypeRegistry->get($filter->getType(), 'default'),
            $filter->getFormOptions(),
        );

        $criteria = $gridView->getParameters()->get('criteria', []);
        $form->submit($criteria);

        $context = array_merge($this->filterContextFactory->create($gridView, $filter), [
            'form' => $form->get($filter->getName())->createView(),
        ]);

        return $this->twig->render($template, $context);
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function getFilterTemplate(Filter $filter): string
    {
        $template = $filter->getTemplate();
        if (null !== $template) {
            return $template;
        }

        $type = $filter->getType();
        if (!isset($this->filterTemplates[$type])) {
            throw new \InvalidArgumentException(sprintf('Missing template for filter type "%s".', $type));
        }

        return $this->filterTemplates[$type];
    }
}
