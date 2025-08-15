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
use Exception;
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
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Composant Livewire pour le formulaire de commande de licence
 *
 * Ce composant gère un formulaire wizard multi-étapes permettant aux clients
 * de commander des licences de produits avec modules et options additionnels.
 * Il intègre Stripe pour le traitement des paiements en mode abonnement.
 *
 * @package App\Livewire\Client\Forms
 * @author Votre Nom
 * @version 1.0.0
 */
class OrderLicenseForm extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    /**
     * Données du formulaire
     *
     * @var array|null
     */
    public ?array $data = [];

    /**
     * Produit sélectionné
     *
     * @var Product|null
     */
    public ?Product $selectedProduct = null;

    /**
     * Modules sélectionnés (IDs)
     *
     * @var array
     */
    public array $selectedModules = [];

    /**
     * Options sélectionnées (IDs)
     *
     * @var array
     */
    public array $selectedOptions = [];

    /**
     * Prix total calculé
     *
     * @var float
     */
    public float $totalPrice = 0;

    /**
     * Taux de TVA appliqué (20% en France)
     *
     * @var float
     */
    private const TAX_RATE = 0.20;

    /**
     * Nombre de mois gratuits pour l'abonnement annuel
     *
     * @var int
     */
    private const YEARLY_FREE_MONTHS = 2;

    /**
     * Initialise le composant
     *
     * @return void
     */
    public function mount(): void
    {
        try {
            $this->form->fill();
        } catch (Exception $e) {
            Log::error('Erreur lors de l\'initialisation du formulaire de commande', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Erreur lors de l\'initialisation du formulaire');
        }
    }

    /**
     * Définit le schéma du formulaire wizard
     *
     * @param Schema $schema
     * @return Schema
     */
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
                                                    '<div class="font-semibold text-lg">' . e($product->name) . '</div>' .
                                                    '<div class="text-sm text-gray-600">' . e($product->description) . '</div>' .
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
                            Select::make('billing_cycle')
                                ->label('Cycle de facturation')
                                ->options([
                                    BillingCycle::MONTHLY->value => 'Mensuel',
                                    BillingCycle::YEARLY->value => 'Annuel (2 mois offerts)',
                                ])
                                ->default(BillingCycle::MONTHLY->value)
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state) {
                                    // Réinitialiser les options incompatibles
                                    $this->resetIncompatibleOptions($state);
                                    // Recalculer le total
                                    $this->calculateTotal();
                                }),

                            Placeholder::make('price_preview')
                                ->label('Aperçu du prix')
                                ->content(function () {
                                    return $this->generatePricePreview();
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
                                ->rows(3)
                                ->maxLength(500),
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
                                            return $this->generateIncludedModulesDisplay();
                                        }),
                                ]),

                            Section::make('Modules optionnels')
                                ->description('Ajoutez des modules supplémentaires à votre licence')
                                ->schema([
                                    CheckboxList::make('optional_modules')
                                        ->label('Modules disponibles')
                                        ->options(function () {
                                            return $this->getOptionalModulesOptions();
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
                                            return $this->getOptionsForType(OptionType::FEATURE);
                                        })
                                        ->live()
                                        ->reactive() // Ajout pour réagir aux changements d'état
                                        ->afterStateUpdated(fn ($state) => $this->updateSelectedOptions('feature', $state))
                                        ->columns(1),
                                ]),

                            Section::make('Options de support')
                                ->schema([
                                    CheckboxList::make('support_options')
                                        ->label('Support et assistance')
                                        ->options(function () {
                                            return $this->getOptionsForType(OptionType::SUPPORT);
                                        })
                                        ->live()
                                        ->reactive() // Ajout pour réagir aux changements d'état
                                        ->afterStateUpdated(fn ($state) => $this->updateSelectedOptions('support', $state))
                                        ->columns(1),
                                ]),

                            Section::make('Options de stockage')
                                ->schema([
                                    CheckboxList::make('storage_options')
                                        ->label('Stockage supplémentaire')
                                        ->options(function () {
                                            return $this->getOptionsForType(OptionType::STORAGE);
                                        })
                                        ->live()
                                        ->reactive() // Ajout pour réagir aux changements d'état
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

    /**
     * Génère l'aperçu du prix selon le cycle de facturation
     *
     * @return HtmlString|string
     */
    private function generatePricePreview(): HtmlString|string
    {
        if (!$this->selectedProduct) {
            return 'Sélectionnez un produit';
        }

        $billingCycle = $this->data['billing_cycle'] ?? BillingCycle::MONTHLY->value;
        $price = $this->selectedProduct->base_price;

        if ($billingCycle === BillingCycle::YEARLY->value) {
            $monthlyPrice = $price;
            $yearlyPrice = $price * (12 - self::YEARLY_FREE_MONTHS);
            $savings = ($monthlyPrice * 12) - $yearlyPrice;

            return new HtmlString(
                '<div class="space-y-2">' .
                '<div class="text-lg font-semibold text-primary-600">' . number_format($yearlyPrice, 2) . '€/an</div>' .
                '<div class="text-sm text-gray-600">Au lieu de ' . number_format($monthlyPrice * 12, 2) . '€/an</div>' .
                '<div class="text-sm text-green-600 font-medium">Économie de ' . number_format($savings, 2) . '€</div>' .
                '</div>'
            );
        }

        return new HtmlString(
            '<div class="text-lg font-semibold text-primary-600">' . number_format($price, 2) . '€/mois</div>'
        );
    }

    /**
     * Génère l'affichage des modules inclus
     *
     * @return HtmlString|string
     */
    private function generateIncludedModulesDisplay(): HtmlString|string
    {
        if (!$this->selectedProduct) {
            return 'Sélectionnez d\'abord un produit';
        }

        $includedModules = $this->selectedProduct->includedModules;

        if ($includedModules->isEmpty()) {
            return 'Aucun module inclus';
        }

        return new HtmlString(
            '<div class="space-y-2">' .
            $includedModules->map(function ($module) {
                return '<div class="flex items-center space-x-2">' .
                       '<span class="w-2 h-2 bg-green-500 rounded-full"></span>' .
                       '<span class="font-medium">' . e($module->name) . '</span>' .
                       '<span class="text-sm text-gray-600">- ' . e($module->description) . '</span>' .
                       '</div>';
            })->join('') .
            '</div>'
        );
    }

    /**
     * Récupère les options des modules optionnels
     *
     * @return array
     */
    private function getOptionalModulesOptions(): array
    {
        if (!$this->selectedProduct) {
            return [];
        }

        return $this->selectedProduct->optionalModules
            ->mapWithKeys(function ($module) {
                $price = $module->pivot->price_override ?? $module->base_price;
                return [
                    $module->id => new HtmlString(
                        '<div class="space-y-1">' .
                        '<div class="flex items-center justify-between">' .
                        '<span class="font-medium">' . e($module->name) . '</span>' .
                        '<span class="text-primary-600 font-semibold">+' . number_format($price, 2) . '€</span>' .
                        '</div>' .
                        '<div class="text-sm text-gray-600">' . e($module->description) . '</div>' .
                        '<div class="text-xs text-gray-500 uppercase">' . $module->category->label() . '</div>' .
                        '</div>'
                    )
                ];
            })
            ->toArray();
    }

    /**
     * Récupère les options pour un type donné
     *
     * @param OptionType $type
     * @return array
     */
    private function getOptionsForType(OptionType $type): array
    {
        if (!$this->selectedProduct) {
            return [];
        }

        // Récupérer le cycle de facturation actuel
        $currentBillingCycle = BillingCycle::from($this->data['billing_cycle'] ?? BillingCycle::MONTHLY->value);

        return $this->selectedProduct->options
            ->where('type', $type)
            ->where('is_active', true)
            ->filter(function ($option) use ($currentBillingCycle) {
                // Si l'option a un cycle de facturation spécifique, vérifier la compatibilité
                if ($option->billing_cycle) {
                    return $option->billing_cycle === $currentBillingCycle;
                }
                // Si pas de cycle spécifique, l'option est disponible pour tous les cycles
                return true;
            })
            ->mapWithKeys(function ($option) {
                return [
                    $option->id => new HtmlString(
                        '<div class="space-y-1">' .
                        '<div class="flex items-center justify-between">' .
                        '<span class="font-medium">' . e($option->name) . '</span>' .
                        '<span class="text-primary-600 font-semibold">+' . number_format($option->price, 2) . '€/' . $option->billing_cycle->label() . '</span>' .
                        '</div>' .
                        '<div class="text-sm text-gray-600">' . e($option->description) . '</div>' .
                        '</div>'
                    )
                ];
            })
            ->toArray();
    }

    /**
     * Met à jour les options sélectionnées pour un type donné
     *
     * @param string $type Type d'option (feature, support, storage)
     * @param array|null $optionIds IDs des options sélectionnées
     * @return void
     */
    public function updateSelectedOptions(string $type, ?array $optionIds): void
    {
        try {
            // Convertir le type string en enum OptionType
            $optionType = OptionType::tryFrom($type);

            // Si le type est valide, supprimer les anciennes options de ce type
            if ($optionType !== null && $this->selectedProduct) {
                $this->selectedOptions = array_filter(
                    $this->selectedOptions,
                    fn($optionId) => !$this->selectedProduct->options
                        ->where('type', $optionType)
                        ->pluck('id')
                        ->contains($optionId)
                );
            }

            // Ajouter les nouvelles options
            if ($optionIds) {
                $this->selectedOptions = array_merge($this->selectedOptions, $optionIds);
            }

            $this->calculateTotal();
        } catch (Exception $e) {
            Log::error('Erreur lors de la mise à jour des options sélectionnées', [
                'type' => $type,
                'optionIds' => $optionIds,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Erreur lors de la sélection des options');
        }
    }

    /**
     * Réinitialise les options incompatibles avec le nouveau cycle de facturation
     *
     * @param string $newBillingCycle
     * @return void
     */
    private function resetIncompatibleOptions(string $newBillingCycle): void
    {
        if (!$this->selectedProduct || empty($this->selectedOptions)) {
            return;
        }

        $newCycle = BillingCycle::from($newBillingCycle);
        $incompatibleOptions = [];

        foreach ($this->selectedOptions as $optionId) {
            $option = $this->selectedProduct->options->find($optionId);
            if ($option && $option->billing_cycle && $option->billing_cycle !== $newCycle) {
                $incompatibleOptions[] = $optionId;
            }
        }

        // Supprimer les options incompatibles
        if (!empty($incompatibleOptions)) {
            $this->selectedOptions = array_diff($this->selectedOptions, $incompatibleOptions);

            // Informer l'utilisateur
            $optionNames = $this->selectedProduct->options
                ->whereIn('id', $incompatibleOptions)
                ->pluck('name')
                ->join(', ');

            session()->flash('warning', "Les options suivantes ont été désélectionnées car elles ne sont pas compatibles avec le cycle de facturation choisi : {$optionNames}");
        }
    }

    /**
     * Calcule le prix total de la commande
     *
     * @return void
     */
    public function calculateTotal(): void
    {
        try {
            if (!$this->selectedProduct) {
                $this->totalPrice = 0;
                return;
            }

            $billingCycle = $this->data['billing_cycle'] ?? BillingCycle::MONTHLY->value;
            $basePrice = $this->selectedProduct->base_price;

            // Appliquer la réduction annuelle
            if ($billingCycle === BillingCycle::YEARLY->value) {
                $basePrice = $basePrice * (12 - self::YEARLY_FREE_MONTHS);
            }

            // Calculer le prix total avec modules et options
            $this->totalPrice = $this->selectedProduct->calculateTotalPrice(
                $this->selectedModules,
                $this->selectedOptions
            );

            // Appliquer la réduction annuelle sur le total
            if ($billingCycle === BillingCycle::YEARLY->value) {
                $monthlyTotal = $this->totalPrice;
                $this->totalPrice = $monthlyTotal * (12 - self::YEARLY_FREE_MONTHS);
            }
        } catch (Exception $e) {
            Log::error('Erreur lors du calcul du prix total', [
                'selectedProduct' => $this->selectedProduct?->id,
                'selectedModules' => $this->selectedModules,
                'selectedOptions' => $this->selectedOptions,
                'error' => $e->getMessage()
            ]);
            $this->totalPrice = 0;
            session()->flash('error', 'Erreur lors du calcul du prix');
        }
    }

    /**
     * Génère le récapitulatif HTML de la commande
     *
     * @return HtmlString
     */
    public function generateOrderSummary(): HtmlString
    {
        if (!$this->selectedProduct) {
            return new HtmlString('Aucun produit sélectionné');
        }

        try {
            $html = '<div class="space-y-4">';

            // Produit principal
            $billingCycle = $this->data['billing_cycle'] ?? BillingCycle::MONTHLY->value;
            $billingLabel = BillingCycle::from($billingCycle)->label();

            $html .= '<div class="border-b pb-2">';
            $html .= '<h4 class="font-semibold">Produit</h4>';
            $html .= '<div class="flex justify-between items-center">';
            $html .= '<span>' . e($this->selectedProduct->name) . ' (' . $billingLabel . ')</span>';
            $html .= '<span class="font-medium">' . number_format($this->selectedProduct->base_price, 2) . '€</span>';
            $html .= '</div>';
            $html .= '</div>';

            // Domaine
            if (!empty($this->data['domain'])) {
                $html .= '<div class="border-b pb-2">';
                $html .= '<h4 class="font-semibold">Domaine</h4>';
                $html .= '<span>' . e($this->data['domain']) . '</span>';
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
                        $html .= '<span>' . e($module->name) . '</span>';
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
                        $html .= '<span>' . e($option->name) . '</span>';
                        $html .= '<span class="font-medium">+' . number_format($option->price, 2) . '€</span>';
                        $html .= '</div>';
                    }
                }
                $html .= '</div>';
            }

            $html .= '</div>';

            return new HtmlString($html);
        } catch (Exception $e) {
            Log::error('Erreur lors de la génération du récapitulatif de commande', [
                'error' => $e->getMessage()
            ]);
            return new HtmlString('Erreur lors de la génération du récapitulatif');
        }
    }

    /**
     * Procède au paiement après validation
     *
     * @return void
     */
    public function proceedToPayment(): void
    {
        try {
            // DÉBOGAGE: Afficher les données actuelles
            Log::info('Début proceedToPayment - Données actuelles', [
                'data' => $this->data,
                'selectedProduct' => $this->selectedProduct?->toArray(),
                'selectedModules' => $this->selectedModules,
                'selectedOptions' => $this->selectedOptions,
                'user_id' => Auth::id()
            ]);

            // Validation des données
            Log::info('Début validation des données');
            $this->validateOrderData();
            Log::info('Validation des données réussie');

            // Récupérer ou créer le client Stripe
            Log::info('Récupération du client Stripe');
            $user = Auth::user();
            $customer = $user->customer; // Récupérer le Customer associé à l'utilisateur

            if (!$customer) {
                throw new Exception('Aucun profil client trouvé pour cet utilisateur');
            }

            if (!$customer->stripe_id) {
                $customer->createAsStripeCustomer();
            }
            Log::info('Client Stripe récupéré', ['stripe_id' => $customer->stripe_id]);

            // Créer la session de checkout pour subscription
            Log::info('Création de la session Stripe');
            $checkoutSession = $this->createStripeSubscriptionCheckout($customer);

            if (!$checkoutSession) {
                throw new Exception('Impossible de créer la session de paiement');
            }

            Log::info('Session Stripe créée avec succès', ['session_id' => $checkoutSession->id]);

            // Rediriger vers Stripe Checkout
            $this->redirect($checkoutSession->url);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();
            Log::error('Erreur de validation détaillée', [
                'errors' => $errors,
                'data' => $this->data,
                'selectedProduct' => $this->selectedProduct?->toArray(),
                'selectedModules' => $this->selectedModules,
                'selectedOptions' => $this->selectedOptions,
                'user_id' => Auth::id()
            ]);
            session()->flash('error', 'Erreur lors de la validation de la commande : ' . implode(', ', $errors));
        } catch (Exception $e) {
            Log::error('Erreur lors du processus de paiement', [
                'user_id' => Auth::id(),
                'product_id' => $this->selectedProduct?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $this->data,
                'selectedModules' => $this->selectedModules,
                'selectedOptions' => $this->selectedOptions
            ]);
            session()->flash('error', 'Erreur lors de la création de l\'abonnement : ' . $e->getMessage());
        }
    }

    /**
     * Valide les données de la commande
     *
     * @return void
     * @throws ValidationException
     */
    private function validateOrderData(): void
    {
        Log::info('Début validateOrderData', [
            'data' => $this->data,
            'selectedProduct' => $this->selectedProduct?->toArray(),
            'selectedModules' => $this->selectedModules,
            'selectedOptions' => $this->selectedOptions
        ]);

        // Validation des champs de base
        try {
            $this->validate([
                'data.product_id' => 'required|exists:products,id',
                'data.billing_cycle' => 'required|in:monthly,yearly',
                'data.domain' => 'required|string|max:255',
                'data.domain_notes' => 'nullable|string|max:500',
            ]);
            Log::info('Validation des champs de base réussie');
        } catch (ValidationException $e) {
            Log::error('Erreur validation champs de base', ['errors' => $e->validator->errors()->all()]);
            throw $e;
        }

        // Validation supplémentaire du produit
        if (!$this->selectedProduct) {
            Log::error('Aucun produit sélectionné');
            throw ValidationException::withMessages([
                'product' => 'Aucun produit sélectionné'
            ]);
        }

        if (!$this->selectedProduct->is_active) {
            Log::error('Produit inactif', ['product_id' => $this->selectedProduct->id]);
            throw ValidationException::withMessages([
                'product' => 'Le produit sélectionné n\'est plus disponible'
            ]);
        }

        // Valider les modules sélectionnés
        if (!empty($this->selectedModules)) {
            Log::info('Validation des modules', ['selectedModules' => $this->selectedModules]);
            foreach ($this->selectedModules as $moduleId) {
                $module = $this->selectedProduct->optionalModules()->find($moduleId);
                if (!$module) {
                    Log::error('Module invalide', ['module_id' => $moduleId]);
                    throw ValidationException::withMessages([
                        'modules' => "Module invalide sélectionné: {$moduleId}"
                    ]);
                }
                Log::info('Module validé', ['module_id' => $moduleId, 'module_name' => $module->name]);
            }
        }

        // Valider les options sélectionnées
        if (!empty($this->selectedOptions)) {
            Log::info('Validation des options', ['selectedOptions' => $this->selectedOptions]);
            foreach ($this->selectedOptions as $optionId) {
                $option = Option::find($optionId);
                if (!$option) {
                    Log::error('Option invalide', ['option_id' => $optionId]);
                    throw ValidationException::withMessages([
                        'options' => "Option invalide sélectionnée: {$optionId}"
                    ]);
                }
                Log::info('Option validée', ['option_id' => $optionId, 'option_name' => $option->name]);
            }
        }

        Log::info('validateOrderData terminée avec succès');
    }

    /**
     * Crée une session de checkout Stripe pour un abonnement
     *
     * @param mixed $customer Client Stripe
     * @return mixed|null Session de checkout Stripe
     */
    protected function createStripeSubscriptionCheckout($customer)
    {
        try {
            $billingCycle = BillingCycle::from($this->data['billing_cycle']);

            // Créer d'abord une facture pour avoir un ID à passer dans les URLs
            $invoice = $this->createInvoice($customer);
            if (!$invoice) {
                throw new Exception('Impossible de créer la facture');
            }

            // Préparer les line items pour la subscription
            $lineItems = $this->prepareStripeLineItems($billingCycle);

            // Créer la session Stripe Checkout pour subscription
            return $customer->stripe()->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'subscription',
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
        } catch (Exception $e) {
            Log::error('Erreur lors de la création de la session Stripe', [
                'customer_id' => $customer->id ?? null,
                'product_id' => $this->selectedProduct?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Erreur lors de la création de la session de paiement : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Prépare les line items pour Stripe
     *
     * @param BillingCycle $billingCycle
     * @return array
     * @throws Exception
     */
    private function prepareStripeLineItems(BillingCycle $billingCycle): array
    {
        $lineItems = [];

        // Produit principal
        $productPriceId = $this->getProductStripePriceId($this->selectedProduct, $billingCycle);
        if (!$productPriceId) {
            throw new Exception('ID de prix Stripe manquant pour le produit');
        }

        $lineItems[] = [
            'price' => $productPriceId,
            'quantity' => 1,
        ];

        // Modules optionnels
        if (!empty($this->selectedModules) && is_array($this->selectedModules)) {
            foreach ($this->selectedModules as $moduleId) {
                $module = $this->selectedProduct->optionalModules()->find($moduleId);
                if ($module) {
                    try {
                        $modulePriceId = $this->getModuleStripePriceId($module, $billingCycle);
                        $lineItems[] = [
                            'price' => $modulePriceId,
                            'quantity' => 1,
                        ];
                    } catch (Exception $e) {
                        Log::error('Erreur avec le module', [
                            'module_id' => $module->id,
                            'error' => $e->getMessage()
                        ]);
                        throw new Exception("Erreur avec le module {$module->name}: {$e->getMessage()}");
                    }
                }
            }
        }

        // Options - CORRECTION: passer le billingCycle
        if (!empty($this->selectedOptions) && is_array($this->selectedOptions)) {
            foreach ($this->selectedOptions as $optionId) {
                $option = Option::find($optionId);
                if ($option) {
                    try {
                        $optionPriceId = $this->getOptionStripePriceId($option, $billingCycle);
                        $lineItems[] = [
                            'price' => $optionPriceId,
                            'quantity' => 1,
                        ];
                    } catch (Exception $e) {
                        Log::error('Erreur avec l\'option', [
                            'option_id' => $option->id,
                            'error' => $e->getMessage()
                        ]);
                        throw new Exception("Erreur avec l'option {$option->name}: {$e->getMessage()}");
                    }
                }
            }
        }

        return $lineItems;
    }

    /**
     * Récupère l'ID du prix Stripe pour un produit
     *
     * @param Product $product
     * @param BillingCycle $billingCycle
     * @return string|null
     */
    private function getProductStripePriceId(Product $product, BillingCycle $billingCycle): ?string
    {
        try {
            $priceId = $billingCycle === BillingCycle::YEARLY
                ? $product->stripe_price_id_yearly
                : $product->stripe_price_id_monthly;

            if (empty($priceId)) {
                throw new Exception("Prix Stripe {$billingCycle->value} manquant pour le produit: {$product->name}");
            }

            return $priceId;
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération de l\'ID du prix Stripe du produit', [
                'product_id' => $product->id,
                'billing_cycle' => $billingCycle->value,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Erreur lors de la récupération du prix du produit : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère l'ID du prix Stripe pour un module
     *
     * @param Module $module
     * @param BillingCycle $billingCycle
     * @return string
     * @throws Exception
     */
    private function getModuleStripePriceId(Module $module, BillingCycle $billingCycle): string
    {
        try {
            $priceId = $billingCycle === BillingCycle::YEARLY
                ? $module->stripe_price_id_yearly
                : $module->stripe_price_id_monthly;

            if (empty($priceId)) {
                throw new Exception("Prix Stripe {$billingCycle->value} manquant pour le module: {$module->name}");
            }

            return $priceId;
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération de l\'ID du prix Stripe du module', [
                'module_id' => $module->id,
                'billing_cycle' => $billingCycle->value,
                'error' => $e->getMessage()
            ]);
            throw new Exception('Erreur lors de la récupération du prix du module : ' . $e->getMessage());
        }
    }

    /**
     * Récupère l'ID du prix Stripe pour une option
     *
     * @param Option $option
     * @param BillingCycle $billingCycle
     * @return string
     * @throws Exception
     */
    private function getOptionStripePriceId(Option $option, BillingCycle $billingCycle): string
    {
        try {
            // Utiliser le cycle de facturation de la commande, pas celui de l'option
            $priceId = $billingCycle === BillingCycle::YEARLY
                ? $option->stripe_price_id_yearly
                : $option->stripe_price_id_monthly;

            if (empty($priceId)) {
                throw new Exception("Prix Stripe {$billingCycle->value} manquant pour l'option: {$option->name}");
            }

            return $priceId;
        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération de l\'ID du prix Stripe de l\'option', [
                'option_id' => $option->id,
                'billing_cycle' => $billingCycle->value,
                'error' => $e->getMessage()
            ]);
            throw new Exception('Erreur lors de la récupération du prix de l\'option : ' . $e->getMessage());
        }
    }

    /**
     * Crée une facture pour la commande
     *
     * @param mixed $customer
     * @return Invoice|null
     */
    protected function createInvoice($customer): ?Invoice
    {
        try {
            $billingCycle = BillingCycle::from($this->data['billing_cycle']);
            $issuedAt = now();
            $dueDate = $issuedAt->copy()->addDays(30);

            // Créer la facture
            $invoice = Invoice::create([
                'customer_id' => $customer->id,
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'status' => InvoiceStatus::PENDING,
                'due_date' => $dueDate,
                'subtotal_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'currency' => 'EUR',
                'metadata' => [
                    'order_type' => 'license_order',
                    'product_id' => $this->selectedProduct->id,
                    'billing_cycle' => $billingCycle->value,
                    'domain' => $this->data['domain'],
                ],
            ]);

            // Ajouter les items de facture
            $subtotal = $this->addInvoiceItems($invoice, $billingCycle);

            // Calculer la TVA et le total
            $taxAmount = $subtotal * self::TAX_RATE;
            $total = $subtotal + $taxAmount;

            // Mettre à jour les totaux de la facture
            $invoice->update([
                'subtotal_amount' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $total,
            ]);

            return $invoice;
        } catch (Exception $e) {
            Log::error('Erreur lors de la création de la facture', [
                'customer_id' => $customer->id ?? null,
                'product_id' => $this->selectedProduct?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Erreur lors de la création de la facture : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Ajoute les items à la facture
     *
     * @param Invoice $invoice
     * @param BillingCycle $billingCycle
     * @return float Sous-total
     * @throws Exception
     */
    private function addInvoiceItems(Invoice $invoice, BillingCycle $billingCycle): float
    {
        $subtotal = 0;

        // Ajouter l'item principal (produit)
        $productPrice = $this->selectedProduct->base_price;
        if ($billingCycle === BillingCycle::YEARLY) {
            $productPrice = $productPrice * (12 - self::YEARLY_FREE_MONTHS);
        }

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => $this->selectedProduct->name . ' (' . $billingCycle->label() . ')',
            'quantity' => 1,
            'unit_price' => $productPrice,
            'total_price' => $productPrice,
            'metadata' => [
                'type' => 'product',
                'product_id' => $this->selectedProduct->id,
                'billing_cycle' => $billingCycle->value,
            ],
        ]);

        $subtotal += $productPrice;

        // Ajouter les modules optionnels
        if (!empty($this->selectedModules) && is_array($this->selectedModules)) {
            foreach ($this->selectedModules as $moduleId) {
                $module = $this->selectedProduct->optionalModules()->find($moduleId);
                if ($module) {
                    $modulePrice = $module->pivot->price_override ?? $module->base_price;
                    if ($billingCycle === BillingCycle::YEARLY) {
                        $modulePrice = $modulePrice * (12 - self::YEARLY_FREE_MONTHS);
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
                        $optionPrice = $optionPrice * (12 - self::YEARLY_FREE_MONTHS);
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

        return $subtotal;
    }

    /**
     * Crée une session de checkout Stripe pour un paiement unique
     *
     * @param mixed $customer
     * @param Invoice $invoice
     * @return mixed|null
     */
    protected function createStripeCheckoutSession($customer, Invoice $invoice)
    {
        try {
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
                            'name' => 'TVA (' . (self::TAX_RATE * 100) . '%)',
                        ],
                        'unit_amount' => intval($invoice->tax_amount * 100),
                    ],
                    'quantity' => 1,
                ];
            }

            // Créer la session Stripe Checkout
            return $customer->stripe()->checkout->sessions->create([
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
        } catch (Exception $e) {
            Log::error('Erreur lors de la création de la session de checkout Stripe', [
                'customer_id' => $customer->id ?? null,
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Erreur lors de la création de la session de paiement : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Rendu du composant
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.client.forms.order-license-form');
    }
}
