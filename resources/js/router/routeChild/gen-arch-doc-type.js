import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: '/gen-arch-doc-types',
        name: 'gen-arch-doc-types',
        meta: {
            middleware: [auth,checkAuth]
        },
        component: () => import('../../views/pages/gen-arch-doc-type/index'),
    },
];
