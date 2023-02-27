import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: '/arch-document-dtls',
        name: 'arch-document-dtls',
        meta: {
            middleware: [auth,checkAuth]
        },
        component: () => import('../../views/pages/arch-document-dtl/index'),
    },
];
