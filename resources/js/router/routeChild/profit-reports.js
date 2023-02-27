import auth from "../../middleware/auth";
import checkAuth from "../../middleware/auth-check";

export default [
    {
        path: "/profit-reports",
        name: "profit-reports",
        meta: {
            middleware: [auth, checkAuth],
        },
        component: () => import("../../views/pages/profit-reports/index"),
    },
];
