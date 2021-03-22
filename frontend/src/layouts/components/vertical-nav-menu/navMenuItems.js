export default [
  {
    url: null,
    name: "Manage",
    tag: "5",
    tagColor: "success",
    icon: "HomeIcon",

    submenu: [
      {
        url: '/dashboard/analytics',
        name: "Home",
        slug: "dashboard-analytics",
      },
      {
        url: '/config/closures',
        name: "Manage Closures",
        slug: "dashboard-analytics",
      },
      {
        url: '/config/faculties',
        name: "Manage Faculties",
        slug: "dashboard-analytics",
      },
      {
        url: '/user/user-list',
        name: "Manage User",
        slug: "dashboard-analytics",
      },
      {
        url: '/admin/report-error',
        name: "Report Error",
        slug: "dashboard-analytics",
      }
    ]
  },
]

