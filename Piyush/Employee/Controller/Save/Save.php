<?php
namespace Piyush\Employee\Controller\Save;

    use Magento\Framework\App\Action\Action;
    use Magento\Framework\App\Action\Context;
    use Magento\Framework\Exception\CouldNotSaveException;
    use Magento\Framework\Exception\LocalizedException;
    use Magento\Framework\Exception\NoSuchEntityException;
    use Magento\Framework\View\Result\PageFactory;

    use Piyush\Employee\Api\HamburgerRepositoryInterface;
    use Piyush\Employee\Api\Data\HamburgerInterface;
    class Save extends Action
    {
        protected $_pageFactory;

        protected $_hamburgerRepository;
        private $hamburgerModel;


        public function __construct(
            Context $context,
            PageFactory $pageFactory,
            HamburgerRepositoryInterface $hamburgerRepository,
            HamburgerInterface $hamburgerModel
        ) {
            $this->_pageFactory = $pageFactory;
            $this->_hamburgerRepository = $hamburgerRepository;
            $this->hamburgerModel = $hamburgerModel;
            return parent::__construct($context);
        }

        public function execute()
        {
            $params = $this->getRequest()->getParams();
        // var_dump($params);
        // die;
        $hamburger = $this->hamburgerModel->setData($params);//TODO: Challenge Modify here to support the edit save functionality
       try {
            $this->_hamburgerRepository->save($hamburger);
            $this->messageManager->addSuccessMessage(__("Successfully added the Employee %1", $params['Name']));
       } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__("Something went wrong."));
       }
        /* Redirect back to hero display page */
        $redirect = $this->resultRedirectFactory->create();
        $redirect->setPath('employee');
        return $redirect;
        }
    }