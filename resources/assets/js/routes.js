
// Страницы
import Register from './components/Register.vue';
import Login from './components/Login.vue';
import Dashboard from './components/Dashboard.vue';

import Users from './components/admin/users.vue';
import Responsible from './components/admin/responsible.vue';

import Audit from './components/audit/index.vue';
import AuditResults from './components/audit/index_results.vue';
import Object from './components/object/index.vue';
import ObjectGroups from './components/object/groups.vue';
import Requirement from './components/requirement/index.vue';
import RequirementGroups from './components/requirement/groups.vue';
import CheckLists from './components/checklist/index.vue';
import CheckListCategories from './components/checklist/groups.vue';
import TaskCalendar from './components/tasks/calendar.vue';



export default [
    {title: 'home', name:'home',  icon: 'home', path: '/home', component: Dashboard, meta: { auth: false } },
    {title: 'login', name:'login', icon: 'input', path: '/login', component: Login, meta: { auth: false } },
    {title: 'register', name:'register', icon: 'lock_open', path: '/register', component: Register, meta: { auth: false } },
    {title: 'users', name:'users', icon: 'supervisor_account', path: '/users', component: Users, meta: { auth: true } },
    {title: 'responsible', name:'responsible', icon: 'person', path: '/responsible', component: Responsible, meta: { auth: true } },
    {divider: true, path: '/', meta: { auth: true }},
    {title: 'checklists', name:'checklists', icon: 'check', path: '/checklists', component: CheckLists, meta: { auth: true }},
    {title: 'checklist_categories', name:'checklist_categories', icon: 'playlist_add_check', path: '/checklist_categories', component: CheckListCategories, meta: { auth: true }},
    {divider: true, path: '/', meta: { auth: true }},
    {title: 'requirements', name:'requirements', icon: 'assignment_turned_in', path: '/requirements', component: Requirement, meta: { auth: true }},
    {title: 'requirement_groups', name:'requirement_groups', icon: 'assignment', path: '/requirement_groups', component: RequirementGroups, meta: { auth: true }},
    {divider: true, path: '/', meta: { auth: true }},
    {title: 'objects', name:'objects', icon: 'store', path: '/objects', component: Object, meta: { auth: true }},
    {title: 'object_groups', name:'object_groups', icon: 'location_city', path: '/objects_groups', component: ObjectGroups, meta: { auth: true }},
    {divider: true, path: '/', meta: { auth: true }},
    {title: 'audits', name:'audits', icon: 'folder', path: '/audits', component: Audit, meta: { auth: true }},
    {title: 'audit_results', name:'audit_results', icon: 'folder_special', path: '/audit_results/:id', component: AuditResults, meta: { auth: true }},
    {title: 'tasks_calendar', name:'tasks_calendar', icon: 'event', path: '/tasks_calendar', component: TaskCalendar, meta: { auth: true }},
];