import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: '/document-fields',
        name: 'document-fields',
        meta: {
            middleware: [auth,checkAuth]
        },
        component: () => import('../../views/pages/document-field/index'),
    },
];
