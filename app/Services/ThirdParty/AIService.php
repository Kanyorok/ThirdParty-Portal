<?php

namespace App\Services\ThirdParty;

use App\Enums\Core\IntegrationsEnum;
use App\Exceptions\ErroredException;
use App\Models\Settings\APICredential;
use EchoLabs\Prism\Contracts\Schema;
use EchoLabs\Prism\Enums\Provider;
use EchoLabs\Prism\Prism;
use EchoLabs\Prism\Schema\ArraySchema;
use EchoLabs\Prism\Schema\NumberSchema;
use EchoLabs\Prism\Schema\ObjectSchema;
use EchoLabs\Prism\Schema\StringSchema;
use Exception;
use SensitiveParameter;
use stdClass;
use Throwable;

class AIService
{
    private Provider $provider;
    private string $model;
    private array $config = [];

    /**
     * @throws ErroredException
     */
    public function __construct()
    {
        $cred = APICredential::query()->where('Integration', IntegrationsEnum::LLM->value)->latest('Id')->first();
        if (! $cred instanceof APICredential) {
            throw new ErroredException('no LLM configuration Found');
        }
        $this->provider = $this->decodeProvider($cred->Configuration?->Provider);
        $this->model = $cred->Configuration?->Model;
        if ($cred->Configuration?->Config instanceof stdClass) {
            $this->config = get_object_vars($cred->Configuration->Config);
        }
    }

    /**
     * @throws ErroredException
     */
    protected function decodeProvider(string $provider): Provider
    {
        try {
            return Provider::from($provider);
        } catch (Exception | Throwable) {
        }

        throw new ErroredException('Invalid LLM Provider given');
    }

    public static function hasValid(): bool
    {
        try {
            new self();

            return true;
        } catch (Exception | Throwable) {
        }

        return false;
    }

    public static function testConfig(Provider $provider, string $model, #[SensitiveParameter] string $ApiKey): bool
    {
        $response = Prism::structured()
            ->using($provider->value, $model)
            ->withPrompt('create an array of odd numbers between 1 and 100')
            ->usingProviderConfig(['api_key' => $ApiKey])
            ->withClientOptions(['timeout' => 300])->withSchema(new ObjectSchema(
                name: 'odd_numbers',
                description: 'array of odd numbers between 1 and 100',
                properties: [
                    new ArraySchema(
                        name: 'numbers',
                        description: 'array of odd numbers between 1 and 100.',
                        items: new ObjectSchema(
                            name: 'product',
                            description: 'An explanation of the product. a product is a loan, account ot saving account offered',
                            properties: [
                                new NumberSchema('number', 'odd numbers'),
                            ],
                            requiredFields: ['number'],
                        )
                    ),
                ],
                requiredFields: ['numbers']
            ))->generate();

        return (is_array($response->structured));
    }

    public function competitor(string $content): ?array
    {
        return $this->_prompt(
            new ObjectSchema(
                name: 'company_info',
                description: 'A structured information about a company',
                properties: [
                    new StringSchema('name', 'Name of the company  or Site Owner Name', true),
                    new NumberSchema('clients', 'Number of members or clients or client base or membership  listed or Happy members, approximate is acceptable', true),
                    new StringSchema('summary', 'Brief summary about the company / site owner'),
                    new StringSchema('email', 'Email of the site owner of the company or the contact email of the company or support email of the company'),
                    new StringSchema('core_business', 'The core business of the company as listed in the site.'),
                    new ArraySchema(
                        name: 'products',
                        description: 'products the company offers. products involve loans the company offers,  accounts and saving accounts the company has',
                        items: new ObjectSchema(
                            name: 'product',
                            description: 'An explanation of the product. a product is a loan, account ot saving account offered',
                            properties: [
                                new StringSchema('name', 'Name of the product'),
                                new NumberSchema('interest', 'Interest rate of thr product, per annum (per year)', true),
                                new NumberSchema('period', 'the maximum repayment period of the product when its a loan.', true),
                                new StringSchema('description', 'A brief description of the product.'),
                                new StringSchema('security', 'Security Required to get the loan product, when not a loan, add requirements to open the product account', true),
                            ],
                            requiredFields: [
                                'name',
                                'interest',
                                'description',
                                'period',
                                'security',
                            ],
                        )
                    ),
                ],
                requiredFields: [
                    'name',
                    'logo',
                    'clients',
                    'email',
                    'summary',
                    'products',
                ]
            ),
            'Using the website content given below, provide a list of products (accounts, savings account and loans) offered. ' . $content,
        );
    }

    protected function _prompt(Schema $schema, string $prompt): ?array
    {
        $response = Prism::structured()
            ->using($this->provider, $this->model)
            ->withPrompt($prompt)
            ->usingProviderConfig($this->config)
            ->withClientOptions(['timeout' => 300])->withSchema($schema)
            ->generate();

        return $response->structured;
    }
}
