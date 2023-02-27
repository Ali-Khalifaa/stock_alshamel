import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: '/document-types',
        name: 'document-types',
        meta: {
            middleware: [auth,checkAuth]
        },
        component: () => import('../../views/pages/document-type/index'),
    },
];
