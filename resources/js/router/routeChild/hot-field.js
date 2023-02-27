import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: '/hot-fields',
        name: 'hot-fields',
        meta: {
            middleware: [auth,checkAuth]
        },
        component: () => import('../../views/pages/hot-field/index'),
    },
];
