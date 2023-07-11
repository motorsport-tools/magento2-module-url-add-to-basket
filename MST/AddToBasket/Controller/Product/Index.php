<?php
namespace MST\AddToBasket\Controller\Product;

use Magento\Framework\App\Action\Action;

class Index extends Action {

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     *  @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\SessionFactory $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->product = $product;
        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
    }
    public function execute()
    {
        try {
           // $params = array();
           //$params['qty'] = '0';//product quantity
            $qty = '0';
            /* Get product id from a URL like /addtobasket/product?items=id|qty,id|qty,id|qty */
            $pIds = explode(',',urldecode($this->getRequest()->getParam('items')));
            if(is_array($pIds) && count($pIds) > 0) {
                //Setup cart
                //Create checkout Session
                $this->cart->truncate(); //Clear previous old cart
                $session = $this->checkoutSession->create();
                //Get the order Quote
                $quote = $session->getQuote();

                foreach($pIds as $value) {

                    $product = explode('|', $value);
                    
                    //$params['qty'] = isset($product[1]) ? $product[1] : '1';
                    $qty = isset($product[1]) ? $product[1] : '1';
                    
                    $_product = isset($product[0]) ? $this->productRepository->getById( $this->product->getIdBySku($product[0]) ) : false;

                    if ($_product) {
                        $quote->addProduct($_product, $qty);
                    }
                }
                //$this->cart->save();
                $this->cartRepository->save($quote); //Save quote
                $session->replaceQuote($quote)->unsLastRealOrderId(); //Add Quote to the cart
                $this->messageManager->addSuccess(__('Add to cart successfully.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addException(
                $e,
                __('%1', $e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addException($e, $e->getMessage());
        }
        /*cart page*/
        $this->getResponse()->setRedirect('/checkout/cart/index');
    }
}