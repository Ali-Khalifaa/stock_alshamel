import auth from "../middleware/auth";
import checkAuth from "../middleware/auth-check";
import login from "./routeChild/login";
import company from "./routeChild/company";
import module from "./routeChild/module";
import screenButton from "./routeChild/screen-button";
import partner from "./routeChild/partner";
import buttons from "./routeChild/buttons";
import screens from "./routeChild/screens";
import hotField from "./routeChild/hot-field";
import workflow from "./routeChild/workflow";
import documentType from "./routeChild/document-type";
import companyModule from "./routeChild/company-model";
import documentField from "./routeChild/document-field";
import archiveClosedReference from "./routeChild/archive-closed-reference";
import archDepartment from "./routeChild/arch-department";
import archDocument from "./routeChild/arch-document";
import genArchDocType from "./routeChild/gen-arch-doc-type";
import archDocTypeField from "./routeChild/arch-doc-type-field";
import archDocumentDtl from "./routeChild/arch-document-dtl";
import archDocStatus from "./routeChild/arch-doc-status";
import wallet from "./routeChild/wallet";
import stockSalePurchase from "./routeChild/stock-sale-purchase";
import stockMarket from "./routeChild/stock-market";
import stockSector from "./routeChild/stock-sector";
import stock from "./routeChild/stock";
import openningBalance from "./routeChild/openning-balance";
import closingBalance from "./routeChild/closing-balance";
import allTransactions from "./routeChild/all-transactions";
import profitReport from "./routeChild/profit-reports";
import realizedUnrealized from "./routeChild/realized-unrealized";
import openningBalanceEdit from "./routeChild/openning-balance-edit";
import closingBalanceEdit from "./routeChild/closing-balance-edit";
import stocks from "./routeChild/stocks";

export default [
    ...login,
    ...company,
    ...module,
    ...workflow,
    ...hotField,
    ...documentType,
    ...screenButton,
    ...partner,
    ...buttons,
    ...screens,
    ...companyModule,
    ...documentField,
    ...archiveClosedReference,
    ...archDepartment,
    ...archDocument,
    ...genArchDocType,
    ...archDocTypeField,
    ...archDocumentDtl,
    ...archDocStatus,
    ...wallet,
    ...stockSalePurchase,
    ...stockMarket,
    ...stockSector,
    ...stock,
    ...openningBalance,
    ...closingBalance,
    ...allTransactions,
    ...profitReport,
    ...realizedUnrealized,
    ...openningBalanceEdit,
    ...closingBalanceEdit,
    ...stocks,
    //**********************************************
    {
        path: "/",
        name: "home",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/dashboard/sales/index"),
    },
    {
        path: "/dashboard/crm",
        name: "crm-dashboard",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/dashboard/crm/index"),
    },
    {
        path: "/dashboard/analytics",
        name: "analytics-dashboard",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/dashboard/analytics/index"),
    },
    {
        path: "/apps/calendar",
        name: "calendar",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/apps/calendar/index"),
    },
    {
        path: "/apps/chat",
        name: "chat",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/apps/chat"),
    },
    {
        path: "/apps/companies",
        name: "companies",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/apps/companies"),
    },
    {
        path: "/apps/tickets",
        name: "tickets",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/apps/tickets"),
    },
    {
        path: "/apps/file-manager",
        name: "file-manager",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/apps/file-manager"),
    },
    {
        path: "/ecommerce/customers",
        name: "customers",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ecommerce/customers/index"),
    },
    {
        path: "/ecommerce/orders",
        name: "orders",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ecommerce/orders/index"),
    },
    {
        path: "/ecommerce/product-detail/:id",
        name: "product-detail",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ecommerce/product-detail"),
    },
    {
        path: "/ecommerce/products",
        name: "products",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ecommerce/products/index"),
    },
    {
        path: "/ecommerce/cart",
        name: "cart",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ecommerce/cart"),
    },
    {
        path: "/ecommerce/checkout",
        name: "checkout",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ecommerce/checkout"),
    },
    {
        path: "/ecommerce/order-detail",
        name: "order-detail",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ecommerce/order-detail"),
    },
    {
        path: "/ecommerce/product-create",
        name: "product-create",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ecommerce/product-create"),
    },
    {
        path: "/ecommerce/products-grid",
        name: "products-grid",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ecommerce/products-grid"),
    },
    {
        path: "/ecommerce/sellers",
        name: "sellers",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ecommerce/sellers"),
    },
    {
        path: "/email/inbox",
        name: "email-inbox",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/email/inbox"),
    },
    {
        path: "/email/reademail/:id",
        name: "reademail",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/email/reademail"),
    },
    {
        path: "/email/templates",
        name: "email-templates",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/email/templates"),
    },
    {
        path: "/task/list",
        name: "task-list",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/task/list"),
    },
    {
        path: "/task/detail",
        name: "task-detail",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/task/detail"),
    },
    {
        path: "/task/kanban",
        name: "kanban-board",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/task/kanban/index"),
    },
    {
        path: "/contacts/list",
        name: "contacts-list",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/contacts/list"),
    },
    {
        path: "/contacts/profile",
        name: "contacts-profile",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/contacts/profile"),
    },
    {
        path: "/auth/login-1",
        name: "auth-login-1",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/authi/login-1"),
    },
    {
        path: "/auth/register-1",
        name: "auth-register-1",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/authi/register-1"),
    },
    {
        path: "/auth/recoverpwd",
        name: "auth-recoverpwd",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/authi/recoverpwd"),
    },
    {
        path: "/auth/lock-screen",
        name: "auth-lock-screen",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/authi/lock-screen"),
    },
    {
        path: "/auth/signin-signup",
        name: "auth-signin-signup",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/authi/signin-signup"),
    },
    {
        path: "/auth/logout-1",
        name: "auth-logout-1",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/authi/logout-1"),
    },
    {
        path: "/auth/login-2",
        name: "auth-login-2",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/authi/login-2"),
    },
    {
        path: "/auth/register-2",
        name: "auth-register-2",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/authi/register-2"),
    },
    {
        path: "/auth/recoverpwd-2",
        name: "auth-recoverpwd-2",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/authi/recoverpwd-2"),
    },
    {
        path: "/auth/lock-screen-2",
        name: "auth-lock-screen-2",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/authi/lock-screen-2"),
    },
    {
        path: "/auth/signin-signup-2",
        name: "auth-signin-signup-2",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/authi/signin-signup-2"),
    },
    {
        path: "/auth/logout-2",
        name: "auth-logout-2",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/authi/logout-2"),
    },
    {
        path: "/extras/starter",
        name: "extras-starter",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/extras/starter"),
    },
    {
        path: "/extras/timeline",
        name: "extras-timeline",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/extras/timeline"),
    },
    {
        path: "/extras/sitemap",
        name: "extras-sitemap",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/extras/sitemap"),
    },
    {
        path: "/extras/invoice",
        name: "extras-invoice",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/extras/invoice"),
    },
    {
        path: "/extras/faqs",
        name: "extras-faqs",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/extras/faqs"),
    },
    {
        path: "/extras/search-results",
        name: "extras-search-results",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/extras/search-results"),
    },
    {
        path: "/extras/pricing",
        name: "extras-pricing",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/extras/pricing"),
    },
    {
        path: "/extras/maintenance",
        name: "extras-maintenance",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/extras/maintenance"),
    },
    {
        path: "/extras/coming-soon",
        name: "extras-coming-soon",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/extras/coming-soon"),
    },
    {
        path: "/extras/lightbox",
        name: "extras-lightbox",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/extras/lightbox"),
    },
    {
        path: "/error/404",
        name: "error-404",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/error/404"),
    },
    {
        path: "/error/500",
        name: "error-500",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/error/500"),
    },
    {
        path: "/error/404-alt",
        name: "error-404-alt",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/error/404-alt"),
    },
    {
        path: "/ui/avatars",
        name: "ui-avatars",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ui/avatars"),
    },
    {
        path: "/ui/buttons",
        name: "ui-buttons",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ui/buttons"),
    },
    {
        path: "/ui/cards",
        name: "ui-cards",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ui/cards"),
    },
    {
        path: "/ui/carousel",
        name: "ui-carousel",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ui/carousel"),
    },
    {
        path: "/ui/dropdowns",
        name: "ui-dropdowns",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ui/dropdowns"),
    },
    {
        path: "/ui/general",
        name: "ui-general",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ui/general"),
    },
    {
        path: "/ui/grid",
        name: "ui-grid",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ui/grid"),
    },
    {
        path: "/ui/images",
        name: "ui-images",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ui/images"),
    },
    {
        path: "/ui/list-group",
        name: "ui-list-group",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ui/list-group"),
    },
    {
        path: "/ui/modals",
        name: "ui-modals",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ui/modals"),
    },
    {
        path: "/ui/notifications",
        name: "ui-notifications",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ui/notifications"),
    },
    {
        path: "/ui/portlets",
        name: "ui-portlets",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ui/portlets"),
    },
    {
        path: "/ui/progress",
        name: "ui-progress",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ui/progress"),
    },
    {
        path: "/ui/ribbons",
        name: "ui-ribbons",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ui/ribbons"),
    },
    {
        path: "/ui/spinners",
        name: "ui-spinners",

        component: () => import("../views/pages/ui/spinners"),
    },
    {
        path: "/ui/tabs-accordions",
        name: "ui-tabs-accordions",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ui/tabs-accordions"),
    },
    {
        path: "/ui/tooltips-popovers",
        name: "ui-tooltips-popovers",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ui/tooltips-popovers"),
    },
    {
        path: "/ui/typography",
        name: "ui-typography",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ui/typography"),
    },
    {
        path: "/ui/video",
        name: "ui-video",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/ui/video"),
    },
    {
        path: "/extended/rangeslider",
        name: "rangeslider",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/extended/rangeslider"),
    },
    {
        path: "/extended/sweet-alert",
        name: "sweet-alert",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/extended/sweet-alert"),
    },
    {
        path: "/extended/tour",
        name: "tour",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/extended/tour"),
    },
    {
        path: "/extended/scrollspy",
        name: "scrollspy",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/extended/scrollspy"),
    },
    {
        path: "/widgets",
        name: "widgets",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/widgets"),
    },
    {
        path: "/icons/feather",
        name: "feather",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/icons/feather"),
    },
    {
        path: "/icons/remix",
        name: "remix",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/icons/remix"),
    },
    {
        path: "/icons/boxicons",
        name: "boxicons",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/icons/boxicons"),
    },
    {
        path: "/icons/mdi",
        name: "mdi",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/icons/mdi"),
    },
    {
        path: "/icons/font-awesome",
        name: "font-awesome",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/icons/font-awesome"),
    },
    {
        path: "/icons/weather",
        name: "weather",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/icons/weather"),
    },
    {
        path: "/forms/elements",
        name: "elements",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/forms/elements"),
    },
    {
        path: "/forms/advanced",
        name: "advanced-form",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/forms/advanced"),
    },
    {
        path: "/forms/validation",
        name: "validation",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/forms/validation"),
    },
    {
        path: "/forms/wizard",
        name: "wizard",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/forms/wizard"),
    },
    {
        path: "/forms/mask",
        name: "mask",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/forms/mask"),
    },
    {
        path: "/forms/quill",
        name: "quill",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/forms/quill"),
    },
    {
        path: "/forms/file-uploads",
        name: "file-uploads",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/forms/file-uploads"),
    },
    {
        path: "/tables/basic",
        name: "basic",

        component: () => import("../views/pages/tables/basic"),
    },
    {
        path: "/tables/advanced",
        name: "advanced",

        component: () => import("../views/pages/tables/advanced"),
    },
    {
        path: "/charts/apex",
        name: "charts-apex",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/charts/apex"),
    },
    {
        path: "/charts/chartjs",
        name: "charts-chartjs",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/charts/chartjs"),
    },
    {
        path: "/charts/c3",
        name: "charts-c3",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/charts/c3"),
    },
    {
        path: "/charts/chartist",
        name: "charts-chartist",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/charts/chartist"),
    },
    {
        path: "/charts/knob",
        name: "charts-knob",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/charts/knob"),
    },
    {
        path: "/maps/google",
        name: "maps-google",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../views/pages/maps/google"),
    },
    {
        path: "*",
        name: "page404",
        component: () => import("../views/pages/error/404.vue"),
    },
];
