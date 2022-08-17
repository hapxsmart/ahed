<?php
namespace Aheadworks\Sarp2\Model\Email;

use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Email\Send\ResultFactory;
use Aheadworks\Sarp2\Model\Email\Sender\EnablerInterface;
use Aheadworks\Sarp2\Model\Email\Template\ObjectsInstantiation;
use Aheadworks\Sarp2\Model\Email\Template\Resolver\TemplateResolverInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Sender
 * @package Aheadworks\Sarp2\Model\Email
 */
class Sender implements SenderInterface
{
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var EnablerInterface
     */
    private $enabler;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ObjectsInstantiation
     */
    private $objectInstantiation;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var string
     */
    private $emailTemplatePath;

    /**
     * @var TemplateResolverInterface
     */
    private $emailTemplateResolver;

    /**
     * @var string|null
     */
    private $bccPath;

    /**
     * @param TransportBuilder $transportBuilder
     * @param EnablerInterface $enabler
     * @param Config $config
     * @param ScopeConfigInterface $scopeConfig
     * @param ObjectsInstantiation $objectInstantiation
     * @param ResultFactory $resultFactory
     * @param TemplateResolverInterface|null $emailTemplateResolver
     * @param string $emailTemplatePath
     * @param null $bccPath
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        EnablerInterface $enabler,
        Config $config,
        ScopeConfigInterface $scopeConfig,
        ObjectsInstantiation $objectInstantiation,
        ResultFactory $resultFactory,
        TemplateResolverInterface $emailTemplateResolver = null,
        $emailTemplatePath = '',
        $bccPath = null
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->enabler = $enabler;
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
        $this->objectInstantiation = $objectInstantiation;
        $this->resultFactory = $resultFactory;
        $this->emailTemplateResolver = $emailTemplateResolver;
        $this->emailTemplatePath = $emailTemplatePath;
        $this->bccPath = $bccPath;
    }

    /**
     * {@inheritdoc}
     */
    public function sendIfEnabled(NotificationInterface $notification)
    {
        $storeId = $notification->getStoreId();

        if ($this->enabler->isEnabled($notification)) {
            if ($this->emailTemplateResolver instanceof TemplateResolverInterface) {
                $templateId = $this->emailTemplateResolver->resolve($notification);
            } else {
                $templateId = $this->scopeConfig->getValue(
                    $this->emailTemplatePath,
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                );
            }

            $sendTo = [
                ['email' => $notification->getEmail(), 'name' => $notification->getName()]
            ];

            $bcc = [];
            if ($this->bccPath) {
                $bcc = $this->getBcc($storeId);
            }

            foreach ($sendTo as $recipient) {
                /** @var TransportInterface $transport */
                $transport = $this->transportBuilder
                    ->setTemplateIdentifier($templateId)
                    ->setTemplateModel(Template::class)
                    ->setTemplateOptions(
                        [
                            'area' => Area::AREA_FRONTEND,
                            'store' => $storeId
                        ]
                    )
                    ->setTemplateVars(
                        $this->objectInstantiation->instantiate($notification->getNotificationData())
                    )
                    ->setFrom($this->config->getSenderData($storeId))
                    ->addTo($recipient['email'], $recipient['name'])
                    ->addBcc($bcc)
                    ->getTransport();

                $transport->sendMessage();
            }

            return $this->resultFactory->create(
                ['isSuccessful' => true, 'isDisabled' => false]
            );
        }

        return $this->resultFactory->create(
            ['isSuccessful' => false, 'isDisabled' => true]
        );
    }

    /**
     * Retrieve bcc from config and validate emails
     *
     * @param int $storeId
     * @return array
     */
    private function getBcc($storeId)
    {
        $bcc = [];
        $bccConfig = $this->scopeConfig->getValue(
            $this->bccPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($bccConfig) {
            $bcc = explode(',', (string)$bccConfig);
        }

        return $bcc;
    }
}
