<?php
namespace Piyush\Employee\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

use Piyush\Employee\Api\HamburgerRepositoryInterface;
use Piyush\Employee\Api\Data\HamburgerInterface;
use Piyush\Employee\Model\HamburgerFactory;
use Piyush\Employee\Model\ResourceModel\Hamburger as ObjectResourceModel;
use Piyush\Employee\Model\ResourceModel\Hamburger\CollectionFactory;
/**
 * Class CarsRepository
 */
class HamburgerRepository implements HamburgerRepositoryInterface
{
    protected $objectFactory;
    protected $objectResourceModel;
    protected $collectionFactory;
    protected $searchResultsFactory;

    /**
     * HamburgerRepository constructor.
     *
     * @param HamburgerFactory $objectFactory
     * @param ObjectResourceModel $objectResourceModel
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        HamburgerFactory $objectFactory,
        ObjectResourceModel $objectResourceModel,
        CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->objectFactory        = $objectFactory;
        $this->objectResourceModel  = $objectResourceModel;
        $this->collectionFactory    = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritDoc
     *
     * @throws CouldNotSaveException
     */
    public function save(HamburgerInterface $object)
    {
        try {
            $this->objectResourceModel->save($object);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
        return $object;
    }

    /**
     * @inheritDoc
     */
    public function getById($id)
    {
        $object = $this->objectFactory->create();
        $this->objectResourceModel->load($object, $id);
        if (!$object->getId()) {
            throw new NoSuchEntityException(__('Object with id "%1" does not exist.', $id));
        }
        return $object;
    }

    /**
     * @inheritDoc
     */
    public function delete(HamburgerInterface $object)
    {
        try {
            $this->objectResourceModel->delete($object);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $collection = $this->collectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $objects = [];
        foreach ($collection as $objectModel) {
            $objects[] = $objectModel;
        }
        $searchResults->setItems($objects);
        return $searchResults;
    }
}