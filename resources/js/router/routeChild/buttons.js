import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: '/buttons',
        name: 'buttons',
        meta: {
            middleware: [auth,checkAuth]
        },
        component: () => import('../../views/pages/buttons/index'),
    },
];
