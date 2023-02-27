import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: '/stock-sector',
        name: 'stock-sector',
        meta: {
            middleware: [auth,checkAuth]
        },
        component: () => import('../../views/pages/stock-sector/index'),
    },
];
