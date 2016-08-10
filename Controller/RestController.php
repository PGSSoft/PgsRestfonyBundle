<?php

namespace Pgs\RestfonyBundle\Controller;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Pgs\RestfonyBundle\Doctrine\FilterQueryBuilder;
use Pgs\RestfonyBundle\Event\FormViewEvent;
use Pgs\RestfonyBundle\Event\PostFormEvent;
use Pgs\RestfonyBundle\Event\PutFormEvent;
use Pgs\RestfonyBundle\Event\QueryListEvent;
use Pgs\RestfonyBundle\Event\ResultListEvent;
use Pgs\RestfonyBundle\RestEvents;
use Pgs\RestfonyBundle\Service\RestPaginatorFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * RestController is responsible for execute CRUD action.
 *
 * @author Michał Sikora
 */
abstract class RestController
{
    /**
     * @var RestPaginatorFactory
     */
    private $paginatorFactory;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var array
     */
    private $sortConfiguration = [];

    /**
     * @var ViewHandler
     */
    private $viewHandler;

    /**
     * @var FilterQueryBuilder
     */
    private $filterQueryBuilder;

    /**
     * @param RestManager              $restManager
     * @param FilterQueryBuilder       $filterQueryBuilder
     * @param RestPaginatorFactory     $paginatorFactory
     * @param EventDispatcherInterface $eventDispatcher
     * @param ViewHandler              $viewHandler
     */
    public function __construct(
        RestManager $restManager,
        FilterQueryBuilder $filterQueryBuilder,
        RestPaginatorFactory $paginatorFactory,
        EventDispatcherInterface $eventDispatcher,
        ViewHandler $viewHandler
    ) {
        $this->restManager = $restManager;
        $this->paginatorFactory = $paginatorFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->viewHandler = $viewHandler;
        $this->filterQueryBuilder = $filterQueryBuilder;
    }

    /**
     * @param array $sortConfiguration
     */
    public function setSortConfiguration(array $sortConfiguration)
    {
        $this->sortConfiguration = $sortConfiguration;
    }

    /**
     * List action for crud module.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return Response
     */
    protected function getList(Request $request, ParamFetcherInterface $paramFetcher)
    {
        $sortQuery = $paramFetcher->get('sorts');
        $query = $this->restManager->getManager()->getListQuery($this->sortConfiguration, $sortQuery);
        $formFilter = $this->restManager->getRestFilterFactory()->create();
        $this->filterQueryBuilder->createQueryForListAction($query, $formFilter, $request);
        $this->eventDispatcher->dispatch(
            RestEvents::LIST_ACTION_BEFORE_PAGINATION,
            new QueryListEvent($paramFetcher, $query, $request)
        );
        $results = $this->paginatorFactory->create(
            $query,
            $request,
            $paramFetcher,
            $this->restManager->getModuleName()
        );
        $this->eventDispatcher->dispatch(
            RestEvents::LIST_ACTION_AFTER_PAGINATION,
            new ResultListEvent($paramFetcher, $results)
        );

        return $this->handleWithGroups($results, [
            $this->getSerializationGroup('list'),
            'list',
        ]);
    }

    /**
     * Get entity from given id.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    protected function getOne(Request $request, $id)
    {
        $entity = $this->findOr404($id);
        $this->eventDispatcher->dispatch(RestEvents::GET_ACTION_POST_LOAD, new GenericEvent($entity, [
            'request' => $request,
        ]));

        return $this->handleWithGroups($entity, [
            $this->getSerializationGroup('get'),
        ]);
    }

    /**
     * Create entity from form.
     *
     * @param Request $request
     *
     * @return FormInterface|Response
     */
    protected function post(Request $request)
    {
        $form = $this->restManager->getRestFormFactory()->create($this->restManager->getManager()->create());
        $this->eventDispatcher->dispatch(RestEvents::POST_ACTION_PRE_SUBMIT, new PostFormEvent($form, $request));
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $entity = $form->getData();
            $this->eventDispatcher->dispatch(RestEvents::POST_ACTION_POST_VALIDATION, new GenericEvent($entity));
            $this->restManager->getManager()->persist($entity, true);
            $this->eventDispatcher->dispatch(RestEvents::POST_ACTION_POST_PERSIST, new GenericEvent($entity));

            return $this->handleWithGroups($entity, [
                $this->getSerializationGroup('post'),
            ]);
        }

        $this->eventDispatcher->dispatch(RestEvents::POST_ACTION_VALIDATION_ERROR, new PostFormEvent($form, $request));

        return $this->handleWithGroups($form, [
            $this->getSerializationGroup('post'),
        ]);
    }

    /**
     * Update entity from form.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    protected function put(Request $request, $id)
    {
        $entity = $this->findOr404($id);
        $form = $this->restManager->getRestFormFactory()->create($entity);
        $this->eventDispatcher->dispatch(RestEvents::PUT_ACTION_PRE_SUBMIT, new PutFormEvent($form, $request));
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $entity = $form->getData();
            $this->eventDispatcher->dispatch(RestEvents::PUT_ACTION_POST_VALIDATION, new GenericEvent($entity));
            $this->restManager->getManager()->merge($entity, true);
            $this->eventDispatcher->dispatch(RestEvents::PUT_ACTION_POST_PERSIST, new GenericEvent($entity));

            return $this->handleWithGroups($entity, [
                $this->getSerializationGroup('put'),
            ]);
        }

        $this->eventDispatcher->dispatch(RestEvents::PUT_ACTION_VALIDATION_ERROR, new PutFormEvent($form, $request));

        return $this->handleWithGroups($form, [
            $this->getSerializationGroup('put'),
        ]);
    }

    /**
     * Delete entity from given id.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    protected function delete(Request $request, $id)
    {
        $entity = $this->findOr404($id);
        $this->eventDispatcher->dispatch(RestEvents::DELETE_ACTION_PRE_DELETE, new GenericEvent($entity, [
            'request' => $request,
        ]));
        $this->restManager->getManager()->remove($entity, true);
        $this->eventDispatcher->dispatch(RestEvents::DELETE_ACTION_POST_DELETE, new GenericEvent($entity, [
            'request' => $request,
        ]));

        return $this->handleWithGroups(null, [
            $this->getSerializationGroup('delete'),
        ]);
    }

    /**
     * Show new form schema for crud entity.
     *
     * @param Request $request
     *
     * @return Response
     */
    protected function newForm(Request $request)
    {
        $form = $this->restManager->getRestFormFactory()->create($this->restManager->getManager()->create());
        $this->eventDispatcher->dispatch(RestEvents::NEW_ACTION_LOAD, new FormViewEvent($form, $request));

        return $this->handleWithGroups($form, [
            $this->getSerializationGroup('new'),
        ]);
    }

    /**
     * Show edit form for crud entity.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return FormInterface
     */
    protected function editForm(Request $request, $id)
    {
        $entity = $this->findOr404($id);
        $form = $this->restManager->getRestFormFactory()->create($entity);
        $this->eventDispatcher->dispatch(RestEvents::EDIT_ACTION_LOAD, new FormViewEvent($form, $request));

        return $this->handleWithGroups($form, [
            $this->getSerializationGroup('edit'),
        ]);
    }

    /**
     * This method is responsible for patching field for resource
     * it should be decorating by child controller and defined as patch[field]Action.
     *
     * @param int    $id
     * @param string $fieldName
     * @param mixed  $value
     *
     * @return Response
     */
    protected function patch($id, $fieldName, $value)
    {
        $entity = $this->findOr404($id);

        try {
            $accessor = PropertyAccess::createPropertyAccessor();
            $accessor->setValue($entity, $fieldName, $value);
            $this->eventDispatcher->dispatch(RestEvents::PRE_PATCHED_ACTION, new GenericEvent($entity));
            $this->restManager->getManager()->persist($entity, true);
            $this->eventDispatcher->dispatch(RestEvents::POST_PATCHED_ACTION, new GenericEvent($entity));
        } catch (NoSuchPropertyException $ex) {
            throw new $ex();
        }

        return $this->handleWithGroups($entity, [
            $this->getSerializationGroup('patch'),
        ]);
    }

    /**
     * Apply serialization rules on response object.
     *
     * @param object $result
     * @param array  $groups
     * @param int    $statusCode
     *
     * @return Response
     */
    protected function handleWithGroups($result, array $groups, $statusCode = Response::HTTP_OK)
    {
        $view = View::create($result, $statusCode);
        $view->getContext()->setGroups($groups);
        $this->eventDispatcher->dispatch(RestEvents::HANDLE_VIEW, new GenericEvent($view));

        return $this->viewHandler->handle($view);
    }

    /**
     * Fetch entity from given PK.
     *
     * @param int $id
     *
     * @return object
     *
     * @throws NotFoundHttpException
     */
    protected function findOr404($id)
    {
        $object = $this->restManager->getManager()->find($id);

        if ($object) {
            return $object;
        }

        throw new NotFoundHttpException();
    }

    /**
     * @param string $actionName
     *
     * @return string
     */
    protected function getSerializationGroup($actionName)
    {
        return sprintf('%s_%s', $this->restManager->getModuleName(), $actionName);
    }
}
