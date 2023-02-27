import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: '/arch-documents',
        name: 'arch-documents',
        meta: {
            middleware: [auth,checkAuth]
        },
        component: () => import('../../views/pages/arch-document/index'),
    },
];
