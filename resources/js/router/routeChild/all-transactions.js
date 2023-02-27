import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: "/all-transactions",
        name: "all-transactions",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../../views/pages/all-transactions/index"),
    },
];
