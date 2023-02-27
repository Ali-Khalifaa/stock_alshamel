import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: "/stock",
        name: "stock",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../../views/pages/stock/index"),
    },
];
