import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: '/archive-closed-references',
        name: 'archive-closed-references',
        meta: {
            middleware: [auth,checkAuth]
        },
        component: () => import('../../views/pages/document-field/archive-closed-reference/index'),
    },
];
