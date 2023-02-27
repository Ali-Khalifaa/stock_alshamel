import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: '/company',
        name: 'company',
        meta: {
            middleware: [auth,checkAuth]
        },
        component: () => import('../../views/pages/company/index'),
    },
];
