import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: '/wallet',
        name: 'wallet',
        meta: {
            middleware: [auth,checkAuth]
        },
        component: () => import('../../views/pages/wallet/index'),
    },
];
