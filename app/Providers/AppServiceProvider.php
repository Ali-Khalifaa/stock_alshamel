<?php

namespace App\Providers;

use App\Repositories\Country\CountryInterface;
use App\Repositories\Country\CountryRepository;
use App\Repositories\GenArchDocType\GenArchDocTypeRepository;
use App\Repositories\GenArchDocType\GenArchDocTypeInterface;
use App\Repositories\ArchDocTypeField\ArchDocTypeFieldRepository;
use App\Repositories\ArchDocTypeField\ArchDocTypeFieldInterface;
use App\Repositories\ArchDocumentDtl\ArchDocumentDtlRepository;
use App\Repositories\ArchDocumentDtl\ArchDocumentDtlInterface;
use App\Repositories\ArchDocStatus\ArchDocStatusRepository;
use App\Repositories\ArchDocStatus\ArchDocStatusInterface;

use App\Repositories\ArchDepartment\ArchDepartmentInterface;
use App\Repositories\ArchDepartment\ArchDepartmentRepository;

use App\Repositories\ArchDocument\ArchDocumentInterface;
use App\Repositories\ArchDocument\ArchDocumentRepository;
use App\Repositories\ArchiveClosedReference\ArchiveClosedReferenceInterface;
use App\Repositories\ArchiveClosedReference\ArchiveClosedReferenceRepository;
use App\Repositories\Branch\BranchInterface;
use App\Repositories\Branch\BranchRepository;
use App\Repositories\Branch\BranchRepositoryInterface;
use App\Repositories\Button\ButtonRepository;
use App\Repositories\Button\ButtonRepositoryInterface;
use App\Repositories\CompanyModule\CompanyModuleRepository;
use App\Repositories\CompanyModule\CompanyModuleRepositoryInterface;
use App\Repositories\Company\CompanyInterface;
use App\Repositories\Company\CompanyRepository;
use App\Repositories\DocumentType\DocumentTypeInterface;
use App\Repositories\DocumentType\DocumentTypeRepository;
use App\Repositories\Helpfile\HelpfileRepository;
use App\Repositories\Helpfile\HelpfileRepositoryInterface;
use App\Repositories\Hotfield\HotfieldRepository;
use App\Repositories\Hotfield\HotfieldRepositoryInterface;
use App\Repositories\Module\ModuleInterface;
use App\Repositories\Module\ModuleRepository;
use App\Repositories\Partner\PartnerRepository;
use App\Repositories\Partner\PartnerRepositoryInterface;
use App\Repositories\ScreenButton\ScreenButtonRepository;
use App\Repositories\ScreenButton\ScreenButtonRepositoryInterface;
use App\Repositories\ScreenDocumentType\ScreenDocumentTypeRepository;
use App\Repositories\ScreenDocumentType\ScreenDocumentTypeRepositoryInterface;
use App\Repositories\ScreenHelpfile\ScreenHelpfileRepository;
use App\Repositories\ScreenHelpfile\ScreenHelpfileRepositoryInterface;
use App\Repositories\Screen\ScreenRepository;
use App\Repositories\Screen\ScreenRepositoryInterface;
use App\Repositories\Serial\SerialRepository;
use App\Repositories\Serial\SerialRepositoryInterface;
use App\Repositories\Store\StoreRepository;
use App\Repositories\Store\StoreRepositoryInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\WorkflowTree\WorkflowTreeRepository;
use App\Repositories\WorkflowTree\WorkflowTreeRepositoryInterface;
use App\Repositories\DocumentField\DocumentFieldRepository;
use App\Repositories\DocumentField\DocumentFieldInterface;
use App\Repositories\StockSalePurchaseDetail\StockSalePurchaseDetailInterface;
use App\Repositories\StockSalePurchaseDetail\StockSalePurchaseDetailRepository;
use App\Repositories\Wallet\WalletInterface;
use App\Repositories\Wallet\WalletRepository;
use App\Repositories\StockMarket\StockMarketInterface;
use App\Repositories\StockMarket\StockMarketRepository;
use App\Repositories\StockSector\StockSectorRepository;
use App\Repositories\StockSector\StockSectorInterface;
use App\Repositories\Stock\StockInterface;
use App\Repositories\Stock\StockRepository;
use App\Repositories\Currency\CurrencyRepository;
use App\Repositories\Currency\CurrencyInterface;
use App\Repositories\OpenningBalance\OpenningBalanceInterface;
use App\Repositories\OpenningBalance\OpenningBalanceRepository;
use App\Repositories\ClosingBalance\ClosingBalanceInterface;
use App\Repositories\ClosingBalance\ClosingBalanceRepository;
use App\Repositories\AllTransaction\AllTransactionRepository;
use App\Repositories\AllTransaction\AllTransactionInterface;
use App\Repositories\ProfitReport\ProfitReportRepository;
use App\Repositories\ProfitReport\ProfitReportInterface;
use App\Repositories\RealizedUnrealized\RealizedUnrealizedRepository;
use App\Repositories\RealizedUnrealized\RealizedUnrealizedInterface;


use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(PartnerRepositoryInterface::class, PartnerRepository::class);
        $this->app->bind(ScreenRepositoryInterface::class, ScreenRepository::class);
        $this->app->bind(BranchInterface::class, BranchRepository::class);

        $this->app->bind(HelpfileRepositoryInterface::class, HelpfileRepository::class);
        $this->app->bind(WorkflowTreeRepositoryInterface::class, WorkflowTreeRepository::class);
        $this->app->bind(CompanyModuleRepositoryInterface::class, CompanyModuleRepository::class);
        $this->app->bind(SerialRepositoryInterface::class, SerialRepository::class);
        $this->app->bind(CompanyInterface::class, CompanyRepository::class);
        $this->app->bind(StoreRepositoryInterface::class, StoreRepository::class);
        $this->app->bind(ModuleInterface::class, ModuleRepository::class);
        $this->app->bind(BranchRepositoryInterface::class, BranchRepository::class);
        $this->app->bind(ButtonRepositoryInterface::class, ButtonRepository::class);
        $this->app->bind(ScreenHelpfileRepositoryInterface::class, ScreenHelpfileRepository::class);
        $this->app->bind(DocumentTypeInterface::class, DocumentTypeRepository::class);
        $this->app->bind(ScreenButtonRepositoryInterface::class, ScreenButtonRepository::class);
        $this->app->bind(HotfieldRepositoryInterface::class, HotfieldRepository::class);
        $this->app->bind(ScreenDocumentTypeRepositoryInterface::class, ScreenDocumentTypeRepository::class);
        $this->app->bind(DocumentFieldInterface::class, DocumentFieldRepository::class);
        $this->app->bind(ArchiveClosedReferenceInterface::class, ArchiveClosedReferenceRepository::class);
        $this->app->bind(ArchDepartmentInterface::class, ArchDepartmentRepository::class);
        $this->app->bind(ArchDocumentInterface::class, ArchDocumentRepository::class);
        $this->app->bind(ArchDocStatusInterface::class, ArchDocStatusRepository::class);

        $this->app->bind(GenArchDocTypeInterface::class, GenArchDocTypeRepository::class);
        $this->app->bind(ArchDocTypeFieldInterface::class, ArchDocTypeFieldRepository::class);
        $this->app->bind(ArchDocumentDtlInterface::class, ArchDocumentDtlRepository::class);
        $this->app->bind(WalletInterface::class, WalletRepository::class);
        $this->app->bind(StockSalePurchaseDetailInterface::class, StockSalePurchaseDetailRepository::class);
        $this->app->bind(StockMarketInterface::class, StockMarketRepository::class);
        $this->app->bind(StockInterface::class, StockRepository::class);
        $this->app->bind(StockSectorInterface::class, StockSectorRepository::class);
        $this->app->bind(CurrencyInterface::class, CurrencyRepository::class);
        $this->app->bind(OpenningBalanceInterface::class, OpenningBalanceRepository::class);
        $this->app->bind(ClosingBalanceInterface::class, ClosingBalanceRepository::class);
        $this->app->bind(AllTransactionInterface::class, AllTransactionRepository::class);
        $this->app->bind(ProfitReportInterface::class, ProfitReportRepository::class);
        $this->app->bind(RealizedUnrealizedInterface::class, RealizedUnrealizedRepository::class);
        $this->app->bind(CountryInterface::class, CountryRepository::class);


    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }
}
