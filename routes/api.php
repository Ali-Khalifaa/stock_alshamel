<?php

use App\Http\Controllers\GenArchDocType\GenArchDocTypeController;
use App\Http\Controllers\ArchDocTypeField\ArchDocTypeFieldController;
use App\Http\Controllers\ArchDocumentDtl\ArchDocumentDtlController;
use App\Http\Controllers\ArchDocStatus\ArchDocStatusController;
use App\Http\Controllers\ArchDepartment\ArchDepartmentController;
use App\Http\Controllers\ArchDocument\ArchDocumentController;
use App\Http\Controllers\ArchiveClosedReference\ArchiveClosedReferenceController;
use App\Http\Controllers\Auth\CheckIfValidTokenController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Branch\BranchController;
use App\Http\Controllers\Button\ButtonController;
use App\Http\Controllers\CompanyModule\CompanyModuleController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\DocumentField\DocumentFieldController;
use App\Http\Controllers\DocumentType\DocumentTypeController;
use App\Http\Controllers\Helpfile\HelpfileController;
use App\Http\Controllers\hotfield\hotfieldController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\Partner\PartnerController;
use App\Http\Controllers\ScreenButton\ScreenButtonController;
use App\Http\Controllers\ScreenDocumentType\ScreenDocumentTypeController;
use App\Http\Controllers\ScreenHelpfile\ScreenHelpfileController;
use App\Http\Controllers\Screen\ScreenController;
use App\Http\Controllers\StockSalePurchaseDetail\StockSalePurchaseDetailController;
use App\Http\Controllers\Store\StoreController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Wallet\WalletController;
use App\Http\Controllers\WorkflowTree\WorkflowTreeController;
use App\Http\Controllers\StockMarket\StockMarketController;
use App\Http\Controllers\StockSector\StockSectorController;
use App\Http\Controllers\Stock\StockController;
use App\Http\Controllers\OpenningBalance\OpenningBalanceController;
use App\Http\Controllers\Currency\CurrencyController;
use App\Http\Controllers\ClosingBalance\ClosingBalanceController;
use App\Http\Controllers\AllTransaction\AllTransactionController;
use App\Http\Controllers\ProfitReport\ProfitReportsController;
use  App\Http\Controllers\RealizedUnrealized\RealizedUnrealizedController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::controller(\App\Http\Controllers\MainController::class)->group(function () {
    Route::put("/setting", "setting");
    Route::get("/setting/{user_id}/{screen_id}", "getSetting");
});

Route::get('/users', [UserController::class, "index"]);

/*
 * Auth
 */

Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [LoginController::class, "login"]);
});

Route::group(['prefix' => 'auth', 'middleware' => 'auth:sanctum'], function () {
    Route::post('/logout', [LogoutController::class, "logout"]);
});

Route::group(['prefix' => 'auth', 'middleware' => 'auth:sanctum'], function () {
    Route::post('/check-token', [CheckIfValidTokenController::class, "checkIsValidToken"]);
});

Route::group(['prefix' => 'companies'], function () {
    Route::get('/', [CompanyController::class, "all"])->name('companies.index');
    Route::get('logs/{id}', [CompanyController::class, "logs"])->name('companies.logs');
    Route::get('/{id}', [CompanyController::class, "find"])->name('companies.show');
    Route::post('/', [CompanyController::class, "create"])->name('companies.store');
    Route::post('/{id}', [CompanyController::class, "update"])->name('companies.update');
    Route::delete('/{id}', [CompanyController::class, "destroy"])->name('companies.delete');
    Route::delete('/logs/{id}', [CompanyController::class, "logs"])->name('companies.logs.delete');
    Route::post('bulk-delete', [CompanyController::class, 'bulkDelete']);
});

Route::post('/companyModules/{id}', [CompanyController::class, "companyModules"]);

Route::group(['prefix' => 'stores'], function () {
    Route::get('', [StoreController::class, "index"])->name('stores.index');
    Route::get('logs/{id}', [StoreController::class, "logs"])->name('stores.logs');
    Route::get('/{id}', [StoreController::class, "show"])->name('stores.show');

    Route::post('', [StoreController::class, "store"])->name('stores.store');
    Route::post('/{id}', [StoreController::class, "update"])->name('stores.update');
    Route::delete('/{id}', [StoreController::class, "destroy"])->name('stores.delete');
    Route::post('bulk-delete', [StoreController::class, 'bulkDelete']);
});

Route::group(['prefix' => 'modules'], function () {
    Route::controller(\App\Http\Controllers\Module\ModuleController::class)->group(function () {
        Route::get('/', 'all')->name('modules.index');
        Route::get('/root-nodes', 'getRootNodes')->name('modules.root-nodes');
        Route::get('/child-nodes/{parentId}', 'getChildNodes')->name('modules.child-nodes');
        Route::get('logs/{id}', 'logs')->name('modules.logs');
        Route::get('/{id}', 'find');
        Route::post('/', 'create')->name('modules.create');
        Route::put('/{id}', 'update')->name('modules.update');
        Route::delete('/{id}', 'delete')->name('modules.destroy');
        Route::post('/company', 'addModuleToCompany')->name('modules.company.add');
        Route::get('/{module_id}/company/{company_id}', 'removeModuleFromCompany')->name('modules.company.remove');
        Route::post('bulk-delete', 'bulkDelete');
    });
});

// api of Partners
Route::group(['prefix' => 'partners'], function () {
    Route::controller(PartnerController::class)->group(function () {
        Route::get('/', 'all')->name('partners.index');
        Route::get('/{id}', 'find');
        Route::get('logs/{id}', "logs");
        Route::post('/', 'store')->name('partners.store');
        Route::put('/{id}', 'update')->name('partners.update');
        Route::delete('/{id}', 'delete')->name('partners.destroy');
        Route::post('/companies', 'companies');
        Route::post('/screen-setting', 'screenSetting')->name('partners.screenSetting');
        Route::get('/get-screen-setting/{user_id}/{screen_id}', 'getScreenSetting')->name('partners.getScreenSetting');
        Route::post('/login', 'login');
        Route::post('bulk-delete', 'bulkDelete');
    });
});

// api for screen setting
Route::controller(MainController::class)->group(function () {
    Route::post("/media", "media");
    Route::post("/setting", "setting");
    Route::get("/setting/{user_id}/{screen_id}", "getSetting");
});

// api of screens
Route::group(['prefix' => 'screens'], function () {
    Route::controller(ScreenController::class)->group(function () {
        Route::get('/', 'all')->name('screens.index');
        Route::get("logs/{id}", "logs")->name("screens.logs");
        Route::get('/{id}', 'find');
        Route::get('/screen-documents/{id}', 'getScreenDocumentTypes');
        Route::get('/screen-buttons/{id}', 'getScreenButtons');
        Route::post('/', 'store')->name('screens.store');
        Route::put('/{id}', 'update')->name('screens.update');
        Route::delete('/{id}', 'delete')->name('screens.destroy');
        Route::post('bulk-delete', 'bulkDelete');
    });
});

// api of company_modules
Route::group(['prefix' => 'company-modules'], function () {
    Route::controller(CompanyModuleController::class)->group(function () {
        Route::get('/', 'all')->name('company_modules.index');
        Route::get('/{id}', 'find');
        Route::post('/', 'store')->name('company_modules.store');
        Route::put('/{id}', 'update')->name('company_modules.update');
        Route::delete('/{id}', 'delete')->name('company_modules.destroy');
        Route::get('logs/{id}', 'logs')->name('company_modules.logs');
    });
});
// api of helpfiles
Route::group(['prefix' => 'helpfiles'], function () {
    Route::controller(HelpfileController::class)->group(function () {
        Route::get('/', 'all')->name('helpfiles.index');
        Route::get('/{id}', 'find');
        Route::get('/logs/{id}', 'logs');
        Route::post('/', 'store')->name('helpfiles.store');
        Route::post('/{id}', 'update')->name('helpfiles.update');
        Route::delete('/{id}', 'delete')->name('helpfiles.destroy');
        Route::post('bulk-delete', 'bulkDelete');
    });
});

// api of screenhelpfile
Route::group(['prefix' => 'screen-helpfile'], function () {
    Route::controller(ScreenHelpfileController::class)->group(function () {
        Route::get('/', 'all')->name('screenhelpfile.index');
        Route::get("/logs/{id}", "logs")->name("screenhelpfile.logs");
        Route::get('/{id}', 'find');
        Route::post('/', 'store')->name('screenhelpfile.store');
        Route::post('/{id}', 'update')->name('screenhelpfile.update');
        Route::delete('/{id}', 'delete')->name('screenhelpfile.destroy');
        Route::post('bulk-delete', 'bulkDelete');
    });
});

// api of screenbutton
Route::group(['prefix' => 'screen-button'], function () {
    Route::controller(ScreenButtonController::class)->group(function () {
        Route::get('/', 'all')->name('screenbutton.index');
        Route::get("/logs/{id}", "logs")->name("screenbutton.logs");
        Route::get('/screens', 'allScreens')->name('screenbutton.allScreens');
        Route::get('/{id}', 'find');
        Route::post('/', 'store')->name('screenbutton.store');
        Route::post('/{id}', 'update')->name('screenbutton.update');
        Route::delete('/{id}', 'delete')->name('screenbutton.destroy');
        Route::delete('/remove/{screen_id}/{button_id}', "removeScreenFromButton");
        Route::post('bulk-delete', 'bulkDelete');
    });
});

// api of hotfield
Route::group(['prefix' => 'hotfields'], function () {
    Route::controller(HotfieldController::class)->group(function () {
        Route::get('/', 'all')->name('hotfield.index');
        Route::get('/{id}', 'find');
        Route::get('logs/{id}', "logs");
        Route::post('/', 'store')->name('hotfield.store');
        Route::post('/{id}', 'update')->name('hotfield.update');
        Route::delete('/{id}', 'delete')->name('hotfield.destroy');
        Route::post('bulk-delete', 'bulkDelete');
    });
});

// api of WorkflowTree
Route::group(['prefix' => 'workflow-trees'], function () {
    Route::controller(WorkflowTreeController::class)->group(function () {
        Route::get('/', 'all')->name('WorkflowTree.index');
        Route::get('/root-nodes', 'getRootNodes')->name('workflow.root-nodes');
        Route::get('/child-nodes/{parentId}', 'getChildNodes')->name('workflow.child-nodes');
        Route::get('/{id}', 'find');
        Route::post('/', 'store')->name('WorkflowTree.store');
        Route::put('/{id}', 'update')->name('WorkflowTree.update');
        Route::delete('/{id}', 'delete')->name('WorkflowTree.destroy');
        Route::get('logs/{id}', 'logs')->name('WorkflowTree.logs');
        Route::post('bulk-delete', 'bulkDelete');
    });
});

// // api op serials
//     Route::group(['prefix' => 'serials'], function () {
//         Route::controller(SerialController::class)->group(function () {
//             Route::get('/', 'all')->name('serials.index');
//             Route::get('/show/{id}', 'find');
//             Route::post('/store', 'store')->name('serials.store');
//             Route::put('/update/{id}', 'update')->name('serials.update');
//             Route::delete('/delete/{id}', 'delete')->name('serials.destroy');
//         });
//     });

// api op serials
Route::group(['prefix' => 'buttons'], function () {
    Route::controller(ButtonController::class)->group(function () {
        Route::get('/', 'all')->name('buttons.index');
        Route::get("/logs/{id}", "logs")->name("buttons.logs");
        Route::get('/{id}', 'find');
        Route::post('/', 'store')->name('buttons.store');
        Route::put('/{id}', 'update')->name('buttons.update');
        Route::delete('/{id}', 'delete')->name('buttons.destroy');
        Route::post('bulk-delete', 'bulkDelete');
    });
});

Route::group(['prefix' => 'branches'], function () {
    Route::controller(BranchController::class)->group(function () {
        Route::get('/', 'all')->name('branches.index');
        Route::get('logs/{id}', 'logs')->name('branches.logs');
        Route::get('/{id}', 'find');
        Route::post('/', 'create')->name('branches.create');
        Route::put('/{id}', 'update')->name('branches.update');
        Route::delete('/{id}', 'delete')->name('branches.destroy');
    });
});

Route::group(['prefix' => 'document-type'], function () {
    Route::controller(DocumentTypeController::class)->group(function () {
        Route::get('/', 'all')->name('document.index');
        Route::get('/{id}', 'find');
        Route::post('/', 'create')->name('document.create');
        Route::put('/{id}', 'update')->name('document.update');
        Route::delete('/{id}', 'delete')->name('document.destroy');
        Route::get('logs/{id}', 'logs');
        Route::post('bulk-delete', 'bulkDelete');
    });
});

Route::group(['prefix' => 'document-field'], function () {
    Route::controller(DocumentFieldController::class)->group(function () {
        Route::get('/', 'all')->name('document.index');
        Route::get('/{id}', 'find');
        Route::post('/', 'create')->name('document.create');
        Route::put('/{id}', 'update')->name('document.update');
        Route::delete('/{id}', 'delete')->name('document.destroy');
        Route::get('logs/{id}', 'logs');
        Route::post('bulk-delete', 'bulkDelete');
    });
});

Route::group(['prefix' => 'archive-closed-reference'], function () {
    Route::controller(ArchiveClosedReferenceController::class)->group(function () {
        Route::get('/', 'all')->name('archiveClosedReference.index');
        Route::get('/{id}', 'find');
        Route::post('/', 'create')->name('archiveClosedReference.create');
        Route::put('/{id}', 'update')->name('archiveClosedReference.update');
        Route::delete('/{id}', 'delete')->name('archiveClosedReference.destroy');
        Route::get('logs/{id}', 'logs');
        Route::post('bulk-delete', 'bulkDelete');
    });
});

Route::group(['prefix' => 'arch-department'], function () {
    Route::controller(ArchDepartmentController::class)->group(function () {
        Route::get('/', 'all')->name('archDepartment.index');
        Route::get('/{id}', 'find');
        Route::post('/', 'create')->name('archDepartment.create');
        Route::put('/{id}', 'update')->name('archDepartment.update');
        Route::delete('/{id}', 'delete')->name('archDepartment.destroy');
        Route::get('logs/{id}', 'logs');
        Route::post('bulk-delete', 'bulkDelete');
    });
});

Route::group(['prefix' => 'arch-document'], function () {
    Route::controller(ArchDocumentController::class)->group(function () {
        Route::get('/', 'all')->name('archDocument.index');
        Route::get('/{id}', 'find');
        Route::post('/', 'create')->name('archDocument.create');
        Route::put('/{id}', 'update')->name('archDocument.update');
        Route::delete('/{id}', 'delete')->name('archDocument.destroy');
        Route::get('logs/{id}', 'logs');
        Route::post('bulk-delete', 'bulkDelete');
    });
});

Route::group(['prefix' => 'gen-arch-doc-type'], function () {
    Route::controller(GenArchDocTypeController::class)->group(function () {
        Route::get('/', 'all')->name('gen-arch-doc-type.index');
        Route::get('/{id}', 'find');
        Route::post('/', 'create')->name('gen-arch-doc-type.create');
        Route::put('/{id}', 'update')->name('gen-arch-doc-type.update');
        Route::delete('/{id}', 'delete')->name('gen-arch-doc-type.destroy');
        Route::get('logs/{id}', 'logs');
        Route::post('bulk-delete', 'bulkDelete');
    });
});


Route::group(['prefix' => 'arch-doc-type-field'], function () {
    Route::controller(ArchDocTypeFieldController::class)->group(function () {
        Route::get('/', 'all')->name('arch-doc-type-field.index');
        Route::get('/{id}', 'find');
        Route::post('/', 'create')->name('arch-doc-type-field.create');
        Route::put('/{id}', 'update')->name('arch-doc-type-field.update');
        Route::delete('/{id}', 'delete')->name('arch-doc-type-field.destroy');
        Route::get('logs/{id}', 'logs');
        Route::post('bulk-delete', 'bulkDelete');
    });
});


Route::group(['prefix' => 'arch-document-dtl'], function () {
    Route::controller(ArchDocumentDtlController::class)->group(function () {
        Route::get('/', 'all')->name('arch-document-dtl.index');
        Route::get('/{id}', 'find');
        Route::post('/', 'create')->name('arch-document-dtl.create');
        Route::put('/{id}', 'update')->name('arch-document-dtl.update');
        Route::delete('/{id}', 'delete')->name('arch-document-dtl.destroy');
        Route::get('logs/{id}', 'logs');
        Route::post('bulk-delete', 'bulkDelete');
    });
});

Route::group(['prefix' => 'arch-doc-status'], function () {
    Route::controller(ArchDocStatusController::class)->group(function () {
        Route::get('/', 'all')->name('archDocStatus.index');
        Route::get('/{id}', 'find');
        Route::post('/', 'create')->name('archDocStatus.create');
        Route::put('/{id}', 'update')->name('archDocStatus.update');
        Route::delete('/{id}', 'delete')->name('archDocStatus.destroy');
        Route::get('logs/{id}', 'logs');
        Route::post('bulk-delete', 'bulkDelete');
    });
});

Route::group(['prefix' => 'wallet'], function () {
    Route::controller(WalletController::class)->group(function () {
        Route::get('/', 'all')->name('index');
        Route::get('/{id}', 'find');
        Route::post('/', 'create')->name('create');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('destroy');
    });
});
Route::group(['prefix' => 'stock-market'], function () {
    Route::controller(StockMarketController::class)->group(function () {
        Route::get('/', 'all')->name('stockMarket.index');
        Route::get('/{id}', 'find');
        Route::post('/', 'create')->name('stockMarket.create');
        Route::put('/{id}', 'update')->name('stockMarket.update');
        Route::delete('/{id}', 'delete')->name('stockMarket.destroy');
        Route::get('logs/{id}', 'logs');
        Route::post('bulk-delete', 'bulkDelete');
    });
});

Route::group(['prefix' => 'stock-sale-purchase'], function () {
    Route::controller(StockSalePurchaseDetailController::class)->group(function () {
        Route::get('/', 'all')->name('index');
        Route::get('/{id}', 'find');
        Route::post('/updaterow', 'updaterow')->name('stock-sale-purchase.updaterow');
        Route::post('/updataData', 'updataData')->name('stock-sale-purchase.updataData');
        Route::post('/', 'create')->name('create');
        Route::put('/{id}', 'update')->name('update');
        Route::post('/deleteData', 'deleteData')->name('stock-sale-purchase.deleteData');
    });
});

Route::group(['prefix' => 'stock-sector'], function () {
    Route::controller(StockSectorController::class)->group(function () {
        Route::get('/', 'all')->name('stockSector.index');
        Route::get('/{id}', 'find');
        Route::post('/', 'create')->name('stockSector.create');
        Route::put('/{id}', 'update')->name('stockSector.update');
        Route::delete('/{id}', 'delete')->name('stockSector.destroy');
        Route::get('logs/{id}', 'logs');
        Route::post('bulk-delete', 'bulkDelete');
    });
});

Route::group(['prefix' => 'currency'], function () {
    Route::controller(CurrencyController::class)->group(function () {
        Route::get('/', 'all')->name('currency.index');
    });
});

Route::group(['prefix' => 'stock'], function () {
    Route::controller(StockController::class)->group(function () {
        Route::get('/', 'all')->name('stock.index');
        Route::get('/{id}', 'find');
        Route::post('/', 'create')->name('stock.create');
        Route::put('/{id}', 'update')->name('stock.update');
        Route::delete('/{id}', 'delete')->name('stock.destroy');
        Route::get('logs/{id}', 'logs');
        Route::post('bulk-delete', 'bulkDelete');
    });
});
Route::group(['prefix' => 'openning-balance'], function () {
    Route::controller(OpenningBalanceController::class)->group(function () {
        Route::get('/', 'all')->name('openning-balance.index');
        Route::get('/all', 'getAll')->name('openning-balance.getAll');
        Route::get('/{id}', 'find');
        Route::get('checkdate/{id}', 'checkDate')->name('openning-balance.checkdate');
        Route::post('/', 'create')->name('openning-balance.create');
        // Route::put('/', 'update')->name('openning-balance.update');
        Route::post('/updaterow', 'rowUpdate')->name('openning-balance.updaterow');
        Route::delete('/{id}', 'delete')->name('openning-balance.destroy');
        Route::get('/{id}', 'getWalletEntier')->name('openning-balance.getwallet');
        Route::get('logs/{id}', 'logs');
        Route::post('bulk-delete', 'bulkDelete');
    });
});
Route::group(['prefix' => 'closing-balance'], function () {
    Route::controller(ClosingBalanceController::class)->group(function () {
        Route::get('/', 'all')->name('closing-balance.index');
        Route::get('/{id}', 'find');
        Route::post('/', 'create')->name('closing-balance.create');
        // Route::put('/{id}', 'update')->name('closing-balance.update');
        Route::post('/updaterow', 'rowUpdate')->name('closing-balance.updaterow');
        Route::delete('/destroy/{date}', 'delete')->name('closing-balance.destroy');
        Route::get('/getentier/{date}', 'getAllEntier')->name('closing-balance.getentier');
        Route::get('logs/{id}', 'logs');
        Route::post('bulk-delete', 'bulkDelete');
    });
});

Route::group(['prefix' => 'all-transactions'], function () {
    Route::controller(AllTransactionController::class)->group(function () {
        Route::post('/', 'all')->name('all-transactions.index');
    });
});

Route::group(['prefix' => 'profit-reports'], function () {
    Route::controller(ProfitReportsController::class)->group(function () {
        Route::post('/', 'all')->name('profit-reports.index');
    });
});
Route::group(['prefix' => 'realized-unrealized'], function () {
    Route::controller(RealizedUnrealizedController::class)->group(function () {
        Route::post('/', 'all')->name('realized-unrealized.index');
    });
});


Route::group(['prefix' => 'screenDocumentType'], function () {
    Route::post('/add', [ScreenController::class, 'addScreenToDocumentType']);
    Route::delete('/remove/{screen_id}/{documentType_id}', [ScreenController::class, 'removeScreenFromDocumentType']);
    Route::get('logs/{id}', [ScreenController::class, 'logs'])->name('screenDocumentType.logs');
});

//Route::group(['prefix' => 'countries'], function () {
//    Route::controller(\App\Http\Controllers\Country\CountryController::class)->group(function () {
//        Route::get('/', 'all')->name('countries.index');
//        Route::get('logs/{id}', 'logs')->name('countries.logs');
//        Route::get('/{id}', 'find');
//        Route::post('/', 'create')->name('countries.create');
//        Route::put('/{id}', 'update')->name('countries.update');
//        Route::delete('/{id}', 'delete')->name('countries.destroy');
//        Route::post("bulk-delete", "bulkDelete");
//    });
//});

//----------------------------------------------milad routes ------------------------------
Route::get('everything_about_the_company/{company_id}', [WorkflowTreeController::class, 'everything_about_the_company']);
Route::resource('screen-document-type', ScreenDocumentTypeController::class)->except('create', 'edit');
Route::get('screen-document-type/logs/{id}', [ScreenDocumentTypeController::class, 'logs']);
Route::post('screen-document-type/bulk-delete', [ScreenDocumentTypeController::class, 'bulkDelete']);
//----------------------------------------------------------------------------------------------
