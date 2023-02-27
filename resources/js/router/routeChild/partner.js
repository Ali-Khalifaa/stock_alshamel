import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: '/partner',
        name: 'partner',
        meta: {
            middleware: [auth,checkAuth]
        },
        component: () => import('../../views/pages/partner/index'),
    },
];
