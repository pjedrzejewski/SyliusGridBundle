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
use Sylius\Bundle\ResourceBundle\ExpressionLanguage\ExpressionLanguage;
use Sylius\Component\Grid\Definition\Action;
use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\Definition\Filter;
use Sylius\Component\Grid\FieldTypes\FieldTypeInterface;
use Sylius\Component\Grid\Renderer\GridRendererInterface;
use Sylius\Component\Grid\View\GridViewInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

final class TwigGridRenderer implements GridRendererInterface
{
    private Environment $twig;

    private ServiceRegistryInterface $fieldsRegistry;

    private FormFactoryInterface $formFactory;

    private FormTypeRegistryInterface $formTypeRegistry;

    private ContainerInterface $container;

    private ExpressionLanguage $expression;

    private string $defaultTemplate;

    private array $actionTemplates;

    private array $filterTemplates;

    public function __construct(
        Environment $twig,
        ServiceRegistryInterface $fieldsRegistry,
        FormFactoryInterface $formFactory,
        FormTypeRegistryInterface $formTypeRegistry,
        ContainerInterface $container,
        ExpressionLanguage $expression,
        string $defaultTemplate,
        array $actionTemplates = [],
        array $filterTemplates = [],
    ) {
        $this->twig = $twig;
        $this->fieldsRegistry = $fieldsRegistry;
        $this->formFactory = $formFactory;
        $this->formTypeRegistry = $formTypeRegistry;
        $this->container = $container;
        $this->expression = $expression;
        $this->defaultTemplate = $defaultTemplate;
        $this->actionTemplates = $actionTemplates;
        $this->filterTemplates = $filterTemplates;
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

        $options = $resolver->resolve($this->parseOptions($field->getOptions()));

        return $fieldType->render($field, $data, $options);
    }

    private function parseOptions(array $parameters): array
    {
        return array_map(
            /**
             * @param mixed $parameter
             *
             * @return mixed
             */
            function ($parameter) {
                if (is_array($parameter)) {
                    return $this->parseOptions($parameter);
                }

                return $this->parseOption($parameter);
            },
            $parameters,
        );
    }

    /**
     * @param mixed $parameter
     * @param array|object|null $data
     *
     * @return mixed
     */
    private function parseOption($parameter)
    {
        if (!is_string($parameter)) {
            return $parameter;
        }

        if (0 === strpos($parameter, 'expr:')) {
            return $this->parseOptionExpression(substr($parameter, 5));
        }

        return $parameter;
    }

    /**
     * @return mixed
     */
    private function parseOptionExpression(string $expression)
    {
        return $this->expression->evaluate($expression, ['container' => $this->container]);
    }

    public function renderAction(GridViewInterface $gridView, Action $action, $data = null)
    {
        $type = $action->getType();
        if (!isset($this->actionTemplates[$type])) {
            throw new \InvalidArgumentException(sprintf('Missing template for action type "%s".', $type));
        }

        return $this->twig->render($this->actionTemplates[$type], [
            'grid' => $gridView,
            'action' => $action,
            'data' => $data,
        ]);
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

        return $this->twig->render($template, [
            'grid' => $gridView,
            'filter' => $filter,
            'form' => $form->get($filter->getName())->createView(),
        ]);
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
