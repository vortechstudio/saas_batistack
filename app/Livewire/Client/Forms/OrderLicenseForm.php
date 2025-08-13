<?php

namespace App\Livewire\Client\Forms;

use App\Enums\BillingCycle;
use App\Enums\InvoiceStatus;
use App\Enums\ModuleCategory;
use App\Enums\OptionType;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Module;
use App\Models\Option;
use Filament\Actions\Action as ActionsAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class OrderLicenseForm extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $data = [];
    public ?Product $selectedProduct = null;
    public array $selectedModules = [];
    public array $selectedOptions = [];
    public float $totalPrice = 0;

    public function mount()
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('product')
                        ->label('Produit')
                        ->description('Sélectionnez le produit que vous souhaitez acheter')
                        ->icon('heroicon-o-shopping-bag')
                        ->schema([
                            Radio::make('product_id')
                                ->label('Choisissez votre produit')
                                ->options(function () {
                                    return Product::active()
                                        ->get()
                                        ->mapWithKeys(function ($product) {
                                            return [
                                                $product->id => new HtmlString(
                                                    '<div class="space-y-2">' .
                                                    '<div class="font-semibold text-lg">' . $product->name . '</div>' .
                                                    '<div class="text-sm text-gray-600">' . $product->description . '</div>' .
                                                    '<div class="flex items-center space-x-4 text-sm">' .
                                                    '<span class="font-medium text-primary-600">' . number_format($product->base_price, 2) . '€/' . $product->billing_cycle->label() . '</span>' .
                                                    '<span class="text-gray-500">Max ' . $product->max_users . ' utilisateurs</span>' .
                                                    '<span class="text-gray-500">' . $product->max_projects . ' projets</span>' .
                                                    '<span class="text-gray-500">' . ($product->storage_limit / 1024) . 'GB stockage</span>' .
                                                    '</div>' .
                                                    '</div>'
                                                )
                                            ];
                                        })
                                        ->toArray();
                                })
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state) {
                                    $this->selectedProduct = Product::find($state);
                                    $this->calculateTotal();
                                })
                                ->columns(1),
                        ]),

                    Step::make('billing')
                        ->label('Facturation')
                        ->description('Choisissez votre cycle de facturation')
                        ->icon('heroicon-o-credit-card')
                        ->schema([
                            Radio::make('billing_cycle')
                                ->label('Cycle de facturation')
                                ->options([
                                    BillingCycle::MONTHLY->value => new HtmlString(
                                        '<div class="space-y-1">' .
                                        '<div class="font-semibold">Mensuel</div>' .
                                        '<div class="text-sm text-gray-600">Facturé chaque mois</div>' .
                                        '</div>'
                                    ),
                                    BillingCycle::YEARLY->value => new HtmlString(
                                        '<div class="space-y-1">' .
                                        '<div class="font-semibold">Annuel <span class="text-green-600 text-sm">(2 mois gratuits)</span></div>' .
                                        '<div class="text-sm text-gray-600">Facturé annuellement, économisez 17%</div>' .
                                        '</div>'
                                    ),
                                ])
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn () => $this->calculateTotal())
                                ->columns(1),

                            Placeholder::make('price_preview')
                                ->label('Aperçu du prix')
                                ->content(function () {
                                    if (!$this->selectedProduct) return 'Sélectionnez un produit';

                                    $billingCycle = $this->data['billing_cycle'] ?? BillingCycle::MONTHLY->value;
                                    $price = $this->selectedProduct->base_price;

                                    if ($billingCycle === BillingCycle::YEARLY->value) {
                                        $monthlyPrice = $price;
                                        $yearlyPrice = $price * 10; // 2 mois gratuits
                                        return new HtmlString(
                                            '<div class="space-y-2">' .
                                            '<div class="text-lg font-semibold text-primary-600">' . number_format($yearlyPrice, 2) . '€/an</div>' .
                                            '<div class="text-sm text-gray-600">Au lieu de ' . number_format($monthlyPrice * 12, 2) . '€/an</div>' .
                                            '<div class="text-sm text-green-600 font-medium">Économie de ' . number_format(($monthlyPrice * 12) - $yearlyPrice, 2) . '€</div>' .
                                            '</div>'
                                        );
                                    }

                                    return new HtmlString(
                                        '<div class="text-lg font-semibold text-primary-600">' . number_format($price, 2) . '€/mois</div>'
                                    );
                                }),
                        ]),

                    Step::make('domain')
                        ->label('Domaine')
                        ->description('Configurez le domaine de votre licence')
                        ->icon('heroicon-o-globe-alt')
                        ->schema([
                            TextInput::make('domain')
                                ->label('Nom de domaine')
                                ->placeholder('exemple.com')
                                ->helperText('Le domaine sur lequel la licence sera utilisée')
                                ->required()
                                ->suffixIcon('heroicon-o-globe-alt'),

                            Textarea::make('domain_notes')
                                ->label('Notes sur le domaine (optionnel)')
                                ->placeholder('Informations supplémentaires sur l\'utilisation du domaine...')
                                ->rows(3),
                        ]),

                    Step::make('modules')
                        ->label('Modules')
                        ->description('Sélectionnez les modules additionnels')
                        ->icon('heroicon-o-puzzle-piece')
                        ->schema([
                            Section::make('Modules inclus')
                                ->description('Ces modules sont inclus dans votre produit')
                                ->schema([
                                    Placeholder::make('included_modules')
                                        ->content(function () {
                                            if (!$this->selectedProduct) return 'Sélectionnez d\'abord un produit';

                                            $includedModules = $this->selectedProduct->includedModules;

                                            if ($includedModules->isEmpty()) {
                                                return 'Aucun module inclus';
                                            }

                                            return new HtmlString(
                                                '<div class="space-y-2">' .
                                                $includedModules->map(function ($module) {
                                                    return '<div class="flex items-center space-x-2">' .
                                                           '<span class="w-2 h-2 bg-green-500 rounded-full"></span>' .
                                                           '<span class="font-medium">' . $module->name . '</span>' .
                                                           '<span class="text-sm text-gray-600">- ' . $module->description . '</span>' .
                                                           '</div>';
                                                })->join('') .
                                                '</div>'
                                            );
                                        }),
                                ]),

                            Section::make('Modules optionnels')
                                ->description('Ajoutez des modules supplémentaires à votre licence')
                                ->schema([
                                    CheckboxList::make('optional_modules')
                                        ->label('Modules disponibles')
                                        ->options(function () {
                                            if (!$this->selectedProduct) return [];

                                            return $this->selectedProduct->optionalModules
                                                ->mapWithKeys(function ($module) {
                                                    $price = $module->pivot->price_override ?? $module->base_price;
                                                    return [
                                                        $module->id => new HtmlString(
                                                            '<div class="space-y-1">' .
                                                            '<div class="flex items-center justify-between">' .
                                                            '<span class="font-medium">' . $module->name . '</span>' .
                                                            '<span class="text-primary-600 font-semibold">+' . number_format($price, 2) . '€</span>' .
                                                            '</div>' .
                                                            '<div class="text-sm text-gray-600">' . $module->description . '</div>' .
                                                            '<div class="text-xs text-gray-500 uppercase">' . $module->category->label() . '</div>' .
                                                            '</div>'
                                                        )
                                                    ];
                                                })
                                                ->toArray();
                                        })
                                        ->live()
                                        ->afterStateUpdated(function ($state) {
                                            $this->selectedModules = $state ?? [];
                                            $this->calculateTotal();
                                        })
                                        ->columns(2),
                                ]),
                        ]),

                    Step::make('options')
                        ->label('Options')
                        ->description('Personnalisez votre licence avec des options supplémentaires')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
                            Section::make('Options de fonctionnalités')
                                ->schema([
                                    CheckboxList::make('feature_options')
                                        ->label('Fonctionnalités supplémentaires')
                                        ->options(function () {
                                            if (!$this->selectedProduct) return [];

                                            return $this->selectedProduct->options
                                                ->where('type', OptionType::FEATURE)
                                                ->mapWithKeys(function ($option) {
                                                    return [
                                                        $option->id => new HtmlString(
                                                            '<div class="space-y-1">' .
                                                            '<div class="flex items-center justify-between">' .
                                                            '<span class="font-medium">' . $option->name . '</span>' .
                                                            '<span class="text-primary-600 font-semibold">+' . number_format($option->price, 2) . '€/' . $option->billing_cycle->label() . '</span>' .
                                                            '</div>' .
                                                            '<div class="text-sm text-gray-600">' . $option->description . '</div>' .
                                                            '</div>'
                                                        )
                                                    ];
                                                })
                                                ->toArray();
                                        })
                                        ->live()
                                        ->afterStateUpdated(fn ($state) => $this->updateSelectedOptions('feature', $state))
                                        ->columns(1),
                                ]),

                            Section::make('Options de support')
                                ->schema([
                                    CheckboxList::make('support_options')
                                        ->label('Support et assistance')
                                        ->options(function () {
                                            if (!$this->selectedProduct) return [];

                                            return $this->selectedProduct->options
                                                ->where('type', OptionType::SUPPORT)
                                                ->mapWithKeys(function ($option) {
                                                    return [
                                                        $option->id => new HtmlString(
                                                            '<div class="space-y-1">' .
                                                            '<div class="flex items-center justify-between">' .
                                                            '<span class="font-medium">' . $option->name . '</span>' .
                                                            '<span class="text-primary-600 font-semibold">+' . number_format($option->price, 2) . '€/' . $option->billing_cycle->label() . '</span>' .
                                                            '</div>' .
                                                            '<div class="text-sm text-gray-600">' . $option->description . '</div>' .
                                                            '</div>'
                                                        )
                                                    ];
                                                })
                                                ->toArray();
                                        })
                                        ->live()
                                        ->afterStateUpdated(fn ($state) => $this->updateSelectedOptions('support', $state))
                                        ->columns(1),
                                ]),

                            Section::make('Options de stockage')
                                ->schema([
                                    CheckboxList::make('storage_options')
                                        ->label('Stockage supplémentaire')
                                        ->options(function () {
                                            if (!$this->selectedProduct) return [];

                                            return $this->selectedProduct->options
                                                ->where('type', OptionType::STORAGE)
                                                ->mapWithKeys(function ($option) {
                                                    return [
                                                        $option->id => new HtmlString(
                                                            '<div class="space-y-1">' .
                                                            '<div class="flex items-center justify-between">' .
                                                            '<span class="font-medium">' . $option->name . '</span>' .
                                                            '<span class="text-primary-600 font-semibold">+' . number_format($option->price, 2) . '€/' . $option->billing_cycle->label() . '</span>' .
                                                            '</div>' .
                                                            '<div class="text-sm text-gray-600">' . $option->description . '</div>' .
                                                            '</div>'
                                                        )
                                                    ];
                                                })
                                                ->toArray();
                                        })
                                        ->live()
                                        ->afterStateUpdated(fn ($state) => $this->updateSelectedOptions('storage', $state))
                                        ->columns(1),
                                ]),
                        ]),

                    Step::make('summary')
                        ->label('Récapitulatif')
                        ->description('Vérifiez votre commande avant de procéder au paiement')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Section::make('Récapitulatif de votre commande')
                                ->schema([
                                    Placeholder::make('order_summary')
                                        ->content(function () {
                                            return $this->generateOrderSummary();
                                        }),

                                    Placeholder::make('total_price')
                                        ->label('Total')
                                        ->content(function () {
                                            return new HtmlString(
                                                '<div class="text-2xl font-bold text-primary-600">' .
                                                number_format($this->totalPrice, 2) . '€' .
                                                '</div>'
                                            );
                                        }),

                                    ActionsAction::make('proceed_to_payment')
                                            ->label('Procéder au paiement')
                                            ->color('primary')
                                            ->size('lg')
                                            ->icon('heroicon-o-credit-card')
                                            ->action('proceedToPayment'),
                                ]),
                        ]),
                ])
                ->submitAction(null)
                ->skippable(false)
                ->persistStepInQueryString()
            ])
            ->statePath('data');
    }

    public function updateSelectedOptions(string $type, ?array $optionIds): void
    {
        // Supprimer les anciennes options de ce type
        $this->selectedOptions = array_filter(
            $this->selectedOptions,
            fn($optionId) => !$this->selectedProduct->options
                ->where('type', OptionType::from($type))
                ->pluck('id')
                ->contains($optionId)
        );

        // Ajouter les nouvelles options
        if ($optionIds) {
            $this->selectedOptions = array_merge($this->selectedOptions, $optionIds);
        }

        $this->calculateTotal();
    }

    public function calculateTotal(): void
    {
        if (!$this->selectedProduct) {
            $this->totalPrice = 0;
            return;
        }

        $billingCycle = $this->data['billing_cycle'] ?? BillingCycle::MONTHLY->value;
        $basePrice = $this->selectedProduct->base_price;

        // Appliquer la réduction annuelle
        if ($billingCycle === BillingCycle::YEARLY->value) {
            $basePrice = $basePrice * 10; // 2 mois gratuits
        }

        // Calculer le prix total avec modules et options
        $this->totalPrice = $this->selectedProduct->calculateTotalPrice(
            $this->selectedModules,
            $this->selectedOptions
        );

        // Appliquer la réduction annuelle sur le total
        if ($billingCycle === BillingCycle::YEARLY->value) {
            $monthlyTotal = $this->totalPrice;
            $this->totalPrice = $monthlyTotal * 10; // 2 mois gratuits sur le total
        }
    }

    public function generateOrderSummary(): HtmlString
    {
        if (!$this->selectedProduct) {
            return new HtmlString('Aucun produit sélectionné');
        }

        $html = '<div class="space-y-4">';

        // Produit
        $billingCycle = $this->data['billing_cycle'] ?? BillingCycle::MONTHLY->value;
        $billingLabel = BillingCycle::from($billingCycle)->label();

        $html .= '<div class="border-b pb-2">';
        $html .= '<h4 class="font-semibold">Produit</h4>';
        $html .= '<div class="flex justify-between items-center">';
        $html .= '<span>' . $this->selectedProduct->name . ' (' . $billingLabel . ')</span>';
        $html .= '<span class="font-medium">' . number_format($this->selectedProduct->base_price, 2) . '€</span>';
        $html .= '</div>';
        $html .= '</div>';

        // Domaine
        if (!empty($this->data['domain'])) {
            $html .= '<div class="border-b pb-2">';
            $html .= '<h4 class="font-semibold">Domaine</h4>';
            $html .= '<span>' . $this->data['domain'] . '</span>';
            $html .= '</div>';
        }

        // Modules optionnels
        if (!empty($this->selectedModules) && is_array($this->selectedModules)) {
            $html .= '<div class="border-b pb-2">';
            $html .= '<h4 class="font-semibold">Modules optionnels</h4>';
            foreach ($this->selectedModules as $moduleId) {
                $module = Module::find($moduleId);
                if ($module) {
                    $price = $module->pivot->price_override ?? $module->base_price;
                    $html .= '<div class="flex justify-between items-center">';
                    $html .= '<span>' . $module->name . '</span>';
                    $html .= '<span class="font-medium">+' . number_format($price, 2) . '€</span>';
                    $html .= '</div>';
                }
            }
            $html .= '</div>';
        }

        // Options
        if (!empty($this->selectedOptions) && is_array($this->selectedOptions)) {
            $html .= '<div class="border-b pb-2">';
            $html .= '<h4 class="font-semibold">Options</h4>';
            foreach ($this->selectedOptions as $optionId) {
                $option = Option::find($optionId);
                if ($option) {
                    $html .= '<div class="flex justify-between items-center">';
                    $html .= '<span>' . $option->name . '</span>';
                    $html .= '<span class="font-medium">+' . number_format($option->price, 2) . '€</span>';
                    $html .= '</div>';
                }
            }
            $html .= '</div>';
        }

        $html .= '</div>';

        return new HtmlString($html);
    }

    public function proceedToPayment(): void
    {
        // Valider les données
        $this->validate([
            'data.product_id' => 'required|exists:products,id',
            'data.billing_cycle' => 'required|in:monthly,yearly',
            'data.domain' => 'required',
        ]);

        try {
            // Récupérer le client actuel
            $customer = Auth::user()->customer;

            // Créer ou récupérer le client Stripe
            if (!$customer->hasStripeId()) {
                $customer->createAsStripeCustomer([
                    'name' => $customer->stripeName(),
                    'email' => $customer->stripeEmail(),
                    'address' => $customer->stripeAddress(),
                ]);
            }

            // Créer la session de checkout pour subscription
            $checkoutSession = $this->createStripeSubscriptionCheckout($customer);

            // Rediriger vers Stripe Checkout
            $this->redirect($checkoutSession->url);
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la création de l\'abonnement : ' . $e->getMessage());
            return;
        }
    }

    protected function createStripeSubscriptionCheckout($customer)
    {
        $billingCycle = BillingCycle::from($this->data['billing_cycle']);

        // Créer d'abord une facture pour avoir un ID à passer dans les URLs
        $invoice = $this->createInvoice($customer);

        // Préparer les line items pour la subscription
        $lineItems = [];

        // Produit principal
        $productPriceId = $this->getProductStripePriceId($this->selectedProduct, $billingCycle);
        $lineItems[] = [
            'price' => $productPriceId,
            'quantity' => 1,
        ];

        // Modules optionnels
        if (!empty($this->selectedModules) && is_array($this->selectedModules)) {
            foreach ($this->selectedModules as $moduleId) {
                $module = $this->selectedProduct->optionalModules()->find($moduleId);
                if ($module) {
                    $modulePriceId = $this->getModuleStripePriceId($module, $billingCycle);
                    $lineItems[] = [
                        'price' => $modulePriceId,
                        'quantity' => 1,
                    ];
                }
            }
        }

        // Options
        if (!empty($this->selectedOptions) && is_array($this->selectedOptions)) {
            foreach ($this->selectedOptions as $optionId) {
                $option = Option::find($optionId);
                if ($option) {
                    $optionPriceId = $this->getOptionStripePriceId($option);
                    $lineItems[] = [
                        'price' => $optionPriceId,
                        'quantity' => 1,
                    ];
                }
            }
        }

        // Créer la session Stripe Checkout pour subscription
        return $customer->stripe()->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'subscription', // IMPORTANT: mode subscription
            'success_url' => route('client.order.success', ['invoice' => $invoice->id]) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('client.order.cancel', ['invoice' => $invoice->id]),
            'customer' => $customer->stripe_id,
            'metadata' => [
                'invoice_id' => $invoice->id,
                'customer_id' => $customer->id,
                'product_id' => $this->selectedProduct->id,
                'domain' => $this->data['domain'],
                'billing_cycle' => $billingCycle->value,
                'selected_modules' => json_encode($this->selectedModules ?? []),
                'selected_options' => json_encode($this->selectedOptions ?? []),
            ],
            'subscription_data' => [
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'domain' => $this->data['domain'],
                    'product_id' => $this->selectedProduct->id,
                ],
            ],
        ]);
    }

    private function getProductStripePriceId($product, $billingCycle)
    {
        // Retourner l'ID du prix Stripe selon le cycle de facturation
        return $billingCycle === BillingCycle::YEARLY
            ? $product->stripe_price_id_yearly
            : $product->stripe_price_id_monthly;
    }

    protected function createInvoice($customer): Invoice
    {
        // Calculer les dates
        $billingCycle = BillingCycle::from($this->data['billing_cycle']);
        $issuedAt = now();
        $dueDate = $issuedAt->copy()->addDays(30); // 30 jours pour payer

        // Créer la facture
        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_number' => Invoice::generateInvoiceNumber(), // Utiliser la méthode du modèle
            'status' => InvoiceStatus::PENDING,
            'due_date' => $dueDate, // Corriger le nom du champ
            'subtotal_amount' => 0, // Corriger le nom du champ
            'tax_amount' => 0,
            'total_amount' => 0, // Corriger le nom du champ
            'currency' => 'EUR',
            'metadata' => [
                'order_type' => 'license_order',
                'product_id' => $this->selectedProduct->id,
                'billing_cycle' => $billingCycle->value,
                'domain' => $this->data['domain'],
            ],
        ]);

        // Ajouter l'item principal (produit)
        $productPrice = $this->selectedProduct->base_price;
        if ($billingCycle === BillingCycle::YEARLY) {
            $productPrice = $productPrice * 10; // 2 mois gratuits
        }

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => $this->selectedProduct->name . ' (' . $billingCycle->label() . ')',
            'quantity' => 1,
            'unit_price' => $productPrice,
            'total_price' => $productPrice, // Corriger 'total' en 'total_price'
            'metadata' => [
                'type' => 'product',
                'product_id' => $this->selectedProduct->id,
                'billing_cycle' => $billingCycle->value,
            ],
        ]);

        $subtotal = $productPrice;

        // Ajouter les modules optionnels
        if (!empty($this->selectedModules) && is_array($this->selectedModules)) {
            foreach ($this->selectedModules as $moduleId) {
                $module = $this->selectedProduct->optionalModules()->find($moduleId);
                if ($module) {
                    $modulePrice = $module->pivot->price_override ?? $module->base_price;
                    if ($billingCycle === BillingCycle::YEARLY) {
                        $modulePrice = $modulePrice * 10;
                    }

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => 'Module: ' . $module->name . ' (' . $billingCycle->label() . ')',
                        'quantity' => 1,
                        'unit_price' => $modulePrice,
                        'total_price' => $modulePrice,
                        'metadata' => [
                            'type' => 'module',
                            'module_id' => $module->id,
                            'billing_cycle' => $billingCycle->value,
                        ],
                    ]);

                    $subtotal += $modulePrice;
                }
            }
        }

        // Ajouter les options
        if (!empty($this->selectedOptions) && is_array($this->selectedOptions)) {
            foreach ($this->selectedOptions as $optionId) {
                $option = Option::find($optionId);
                if ($option) {
                    $optionPrice = $option->price;
                    if ($billingCycle === BillingCycle::YEARLY && $option->billing_cycle === BillingCycle::MONTHLY) {
                        $optionPrice = $optionPrice * 10;
                    }

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => 'Option: ' . $option->name . ' (' . $option->billing_cycle->label() . ')',
                        'quantity' => 1,
                        'unit_price' => $optionPrice,
                        'total_price' => $optionPrice,
                        'metadata' => [
                            'type' => 'option',
                            'option_id' => $option->id,
                            'billing_cycle' => $option->billing_cycle->value,
                        ],
                    ]);

                    $subtotal += $optionPrice;
                }
            }
        }

        // Calculer la TVA (20% en France)
        $taxRate = 0.20;
        $taxAmount = $subtotal * $taxRate;
        $total = $subtotal + $taxAmount;

        // Mettre à jour les totaux de la facture
        $invoice->update([
            'subtotal_amount' => $subtotal, // Corriger le nom du champ
            'tax_amount' => $taxAmount,
            'total_amount' => $total, // Corriger le nom du champ
        ]);

        return $invoice;
    }

    protected function createStripeCheckoutSession($customer, Invoice $invoice)
    {
        $billingCycle = BillingCycle::from($this->data['billing_cycle']);

        // Préparer les line items pour Stripe
        $lineItems = [];

        foreach ($invoice->invoiceItems as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item->description,
                        'metadata' => $item->metadata ?? [],
                    ],
                    'unit_amount' => intval($item->unit_price * 100), // Convertir en centimes
                ],
                'quantity' => $item->quantity,
            ];
        }

        // Ajouter la TVA comme line item séparé
        if ($invoice->tax_amount > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'TVA (20%)',
                    ],
                    'unit_amount' => intval($invoice->tax_amount * 100),
                ],
                'quantity' => 1,
            ];
        }

        // Créer la session Stripe Checkout
        $checkoutSession = $customer->stripe()->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('client.order.success', ['invoice' => $invoice->id]) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('client.order.cancel', ['invoice' => $invoice->id]),
            'customer' => $customer->stripe_id,
            'metadata' => [
                'invoice_id' => $invoice->id,
                'customer_id' => $customer->id,
                'product_id' => $this->selectedProduct->id,
                'domain' => $this->data['domain'],
                'billing_cycle' => $billingCycle->value,
            ],
            'automatic_tax' => [
                'enabled' => false, // Nous gérons la TVA manuellement
            ],
            'invoice_creation' => [
                'enabled' => true,
                'invoice_data' => [
                    'description' => 'Commande de licence - ' . $this->selectedProduct->name,
                    'metadata' => [
                        'invoice_id' => $invoice->id,
                        'domain' => $this->data['domain'],
                    ],
                    'footer' => 'Merci pour votre commande !',
                ],
            ],
        ]);

        return $checkoutSession;
    }

    public function render()
    {
        return view('livewire.client.forms.order-license-form');
    }
}
