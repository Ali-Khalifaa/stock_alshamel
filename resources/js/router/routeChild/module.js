import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: '/module',
        name: 'module',
        meta: {
            middleware: [auth,checkAuth]
        },
        component: () => import('../../views/pages/module/index'),
    },
];
