import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: '/dashboard/stocks/stock-market',
        name: 'stock-market',
        meta: {
            middleware: [auth,checkAuth]
        },
        component: () => import('../../views/pages/stocks/stock-market'),
    },

];
