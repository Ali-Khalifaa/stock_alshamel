import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: '/workflows',
        name: 'workflow',
        meta: {
            middleware: [auth,checkAuth]
        },
        component: () => import('../../views/pages/workflow/index'),
    },
];
