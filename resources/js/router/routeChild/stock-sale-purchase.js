import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: '/stock-sale-purchase',
        name: 'stock-sale-purchase',
        meta: {
            middleware: [auth,checkAuth]
        },
        component: () => import('../../views/pages/stock-sale-purchase/index'),
    },
];
