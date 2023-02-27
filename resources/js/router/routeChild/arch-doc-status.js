import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: '/arch-doc-status',
        name: 'arch-doc-status',
        meta: {
            middleware: [auth,checkAuth]
        },
        component: () => import('../../views/pages/arch-doc-status/index'),
    },
];
