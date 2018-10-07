
// Страницы
import Register from './components/Register.vue';
import Login from './components/Login.vue';
import Dashboard from './components/Dashboard.vue';

import Users from './components/admin/users.vue';
// import Responsible from './components/admin/responsible.vue';
import Settings from './components/admin/settings.vue';
import Position from './components/admin/position.vue';
import Department from './components/admin/department.vue';

import Audit from './components/audit/index.vue';
import AuditResults from './components/audit/index_results.vue';
import Object from './components/object/index.vue';
import ObjectGroups from './components/object/groups.vue';
import Requirement from './components/requirement/index.vue';
// import RequirementGroups from './components/requirement/groups.vue';
import CheckLists from './components/checklist/index.vue';
import CheckListCategories from './components/checklist/groups.vue';
import TaskCalendar from './components/tasks/calendar.vue';
import TasksList from './components/tasks/index.vue';



export default [
    {title: 'login', name:'login', icon: 'input', path: '/login', component: Login, meta: { auth: false } },
    {title: 'register', name:'register', icon: 'lock_open', path: '/register', component: Register, meta: { auth: false } },

    {title: 'dashboard', name:'dashboard',  icon: 'dashboard', path: '/', component: Dashboard, meta: { auth: true, role_id: [1]  } },
    {divider: true, path: '/', meta: { auth: true, role_id: [1] }},
    {title: 'object_groups', name:'object_groups', icon: 'location_city', path: '/objects_groups', component: ObjectGroups, meta: { auth: true, role_id: [1] }},
    {title: 'objects', name:'objects', icon: 'store', path: '/objects', component: Object, meta: { auth: true, role_id: [1] }},
    {divider: true, path: '/', meta: { auth: true, role_id: [1] }},
    {title: 'checklist_categories', name:'checklist_categories', icon: 'playlist_add_check', path: '/checklist_categories', component: CheckListCategories, meta: { auth: true, role_id: [1,2] }},
    {title: 'checklists', name:'checklists', icon: 'check', path: '/checklists', component: CheckLists, meta: { auth: true, role_id: [1,2] }},
    {title: 'requirements', name:'requirements', icon: 'assignment_turned_in', path: '/requirements', component: Requirement, meta: { auth: true, role_id: [1,2] }},
    {divider: true, path: '/', meta: { auth: true, role_id: [1,2] }},
    {title: 'audits', name:'audits', icon: 'folder', path: '/audits', component: Audit, meta: { auth: true, role_id: [1,2] }},
    {title: 'audit_calendar', name:'audit_calendar', icon: 'event', path: '/tasks_calendar', component: TaskCalendar, meta: { auth: true, role_id: [1,2]}},
    {title: 'tasks_list', name:'tasks_list', icon: 'work', path: '/tasks_list/:id', component: TasksList, meta: { auth: true, role_id: [1,2] /*, badge: 1 */}},
    {divider: true, path: '/', meta: { auth: true, role_id: [1,2] }},
    {title: 'users', name:'users', icon: 'supervisor_account', path: '/users', component: Users, meta: { auth: true, role_id: [1] } },
    {title: 'departments', name:'departments', icon: 'meeting_room', path: '/departments', component: Department, meta: { auth: true, role_id: [1] } },
    {title: 'positions', name:'positions', icon: '\n' +
        'perm_contact_calendar', path: '/positions', component: Position, meta: { auth: true, role_id: [1] } },
    {title: 'settings', name:'settings', icon: 'settings', path: '/settings', component: Settings, meta: { auth: true, role_id: [1] } },

    // Путь не для меню
    {title: 'audit_results', name:'audit_results', icon: 'folder_special', path: '/audit_results/:id', component: AuditResults, meta: { auth: true, no_show: true, role_id: [1,2] }},

    // Удаленные
    // {title: 'responsible', name:'responsible', icon: 'person', path: '/responsible', component: Responsible, meta: { auth: true, role_id: 1 } },
    // {title: 'requirement_groups', name:'requirement_groups', icon: 'assignment', path: '/requirement_groups', component: RequirementGroups, meta: { auth: true, role_id: 1 }},
];