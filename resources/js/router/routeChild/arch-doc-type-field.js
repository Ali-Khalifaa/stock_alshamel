import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: '/arch-doc-type-fields',
        name: 'arch-doc-type-fields',
        meta: {
            middleware: [auth,checkAuth]
        },
        component: () => import('../../views/pages/arch-doc-type-field/index'),
    },
];
