// // Ensure jQuery is loaded before proceeding
// function ensureJQueryLoaded(callback) {
//     if (window.jQuery) {
//         callback();
//     } else {
//         const script = document.createElement('script');
//         script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
//         script.onload = callback;
//         document.head.appendChild(script);
//     }
// }

// // Alpine.js Component Definition
// document.addEventListener('alpine:init', () => {
//     Alpine.data('adminDashboard', () => ({
//         showAddEmployeeModal: false,
//         showEditEmployeeModal: false,
//         showAddCategoryModal: false,
//         isLoading: false,
        
//         init() {
//             ensureJQueryLoaded(() => {
//                 // Initialize all jQuery-dependent components
//                 this.initDateRangePicker();
//                 this.initTabManagement();
//                 this.initFilters();
//                 this.initCSRF();
//                 this.initSearch();
                
//                 // Load initial tab
//                 loadTab('employees');
//             });
//         },
        
//         initDateRangePicker() {
//             $('#dateRangePicker').daterangepicker({
//                 opens: 'right',
//                 autoUpdateInput: false,
//                 locale: {
//                     cancelLabel: 'Clear',
//                     format: 'YYYY-MM-DD'
//                 }
//             });

//             $('#dateRangePicker').on('apply.daterangepicker', (ev, picker) => {
//                 $(ev.target).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
//             });

//             $('#dateRangePicker').on('cancel.daterangepicker', (ev, picker) => {
//                 $(ev.target).val('');
//             });
//         },
        
//         initTabManagement() {
//             $('.dashboard-tab').on('click', (e) => {
//                 e.preventDefault();
//                 const tab = $(e.currentTarget).data('tab');
//                 loadTab(tab);
//             });
//         },
        
//         initFilters() {
//             $('#articleStatusFilter').change(() => {
//                 loadTab('articles');
//             });

//             $('#filterLogsBtn').click(() => {
//                 loadTab('action-logs');
//             });

//             $('#resetLogsFilterBtn').click(() => {
//                 $('#dateRangePicker').val('');
//                 loadTab('action-logs');
//             });
//         },
        
//         initCSRF() {
//             $.ajaxSetup({
//                 headers: {
//                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
//                     'X-Requested-With': 'XMLHttpRequest'
//                 }
//             });
//         },
        
//         initSearch() {
//             initializeSearch('employees');
//             initializeSearch('articles');
//             initializeSearch('categories');
//         },
        
//         showEditEmployee(id, username, department, position) {
//             this.showEditEmployeeModal = true;
//             $('#edit_employee_id').val(id);
//             $('#edit_username').val(username);
//             $('#edit_department').val(department);
//             $('#edit_position').val(position);
//         },
        
//         addEmployee() {
//             this.isLoading = true;
//             const formData = $('#addEmployeeForm').serialize();
            
//             $.ajax({
//                 url: '/admin/employees',
//                 type: 'POST',
//                 data: formData,
//                 success: (response) => {
//                     this.showAddEmployeeModal = false;
//                     this.isLoading = false;
//                     showNotification('Success', 'Employee added successfully!', 'success');
//                     loadTab('employees');
//                 },
//                 error: (xhr) => {
//                     this.isLoading = false;
//                     showNotification('Error', xhr.responseJSON.message || 'Failed to add employee', 'danger');
//                 }
//             });
//         },
        
//         updateEmployee() {
//             this.isLoading = true;
//             const formData = $('#editEmployeeForm').serialize();
//             const employeeId = $('#edit_employee_id').val();
            
//             $.ajax({
//                 url: `/admin/employees/${employeeId}`,
//                 type: 'PUT',
//                 data: formData,
//                 success: (response) => {
//                     this.showEditEmployeeModal = false;
//                     this.isLoading = false;
//                     showNotification('Success', 'Employee updated successfully!', 'success');
//                     loadTab('employees');
//                 },
//                 error: (xhr) => {
//                     this.isLoading = false;
//                     showNotification('Error', xhr.responseJSON.message || 'Failed to update employee', 'danger');
//                 }
//             });
//         },
        
//         addCategory() {
//             this.isLoading = true;
//             const formData = $('#addCategoryForm').serialize();
            
//             $.ajax({
//                 url: '/admin/categories',
//                 type: 'POST',
//                 data: formData,
//                 success: (response) => {
//                     this.showAddCategoryModal = false;
//                     this.isLoading = false;
//                     showNotification('Success', 'Category added successfully!', 'success');
//                     loadTab('categories');
//                 },
//                 error: (xhr) => {
//                     this.isLoading = false;
//                     showNotification('Error', xhr.responseJSON.message || 'Failed to add category', 'danger');
//                 }
//             });
//         }
//     }));
// });

// // Tab and Data Loading Functions
// function loadTab(tab, page = 1) {
//     // Update active tab UI
//     $('.dashboard-tab').removeClass('active');
//     $(`.dashboard-tab[data-tab="${tab}"]`).addClass('active');
    
//     // Hide all tab contents and show the active one
//     $('.tab-content').removeClass('active');
//     $(`#${tab}Tab`).addClass('active');
    
//     // Show loading state
//     $(`#${tab}TableBody`).html('<tr><td colspan="5" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');

//     let url = `/admin/${tab}/data?page=${page}`;
//     const search = $(`#${tab}Search`).val();
    
//     // Add search filters if they exist
//     if (search) {
//         url += `&search=${encodeURIComponent(search)}`;
//     }

//     // Special filters
//     if (tab === 'articles') {
//         const status = $('#articleStatusFilter').val();
//         if (status) url += `&status=${status}`;
//     } else if (tab === 'action-logs') {
//         const dateRange = $('#dateRangePicker').val();
//         if (dateRange) {
//             const dates = dateRange.split(' - ');
//             url += `&date_from=${dates[0]}&date_to=${dates[1]}`;
//         }
//     }

//     $.ajax({
//         url: url,
//         type: 'GET',
//         success: function(response) {
//             renderTable(tab, response);
//             if (response.stats) {
//                 updateStats(response.stats);
//             }
//         },
//         error: function(xhr) {
//             $(`#${tab}TableBody`).html('<tr><td colspan="5" class="text-center py-4 text-red-500">Failed to load data</td></tr>');
//             console.error(`Error loading ${tab}:`, xhr.responseText);
//         }
//     });
// }

// function renderTable(tab, data) {
//     const tbody = $(`#${tab}TableBody`);
//     tbody.empty();

//     if (data.data.length === 0) {
//         tbody.html('<tr><td colspan="5" class="text-center py-4">No records found</td></tr>');
//         return;
//     }

//     // Render rows based on tab type
//     switch(tab) {
//         case 'employees':
//             renderEmployeeRows(data.data, tbody);
//             break;
//         case 'articles':
//             renderArticleRows(data.data, tbody);
//             break;
//         case 'categories':
//             renderCategoryRows(data.data, tbody);
//             break;
//         case 'action-logs':
//             renderActionLogRows(data.data, tbody);
//             break;
//     }

//     // Update pagination
//     $(`#${tab}Pagination`).html(data.links);
// }

// function renderEmployeeRows(employees, tbody) {
//     employees.forEach(employee => {
//         const hireDate = new Date(employee.hire_date);
//         const formattedHireDate = hireDate.toLocaleDateString('en-US', {
//             year: 'numeric', 
//             month: 'short', 
//             day: 'numeric'
//         });

//         const row = `
//             <tr class="hover:bg-gray-50">
//                 <td class="px-6 py-4 whitespace-nowrap">${employee.staff.username}</td>
//                 <td class="px-6 py-4 whitespace-nowrap">${employee.staff.email}</td>
//                 <td class="px-6 py-4 whitespace-nowrap">${employee.position}</td>
//                 <td class="px-6 py-4 whitespace-nowrap">${formattedHireDate}</td>
//                 <td class="px-6 py-4 whitespace-nowrap">
//                     <div class="flex space-x-2">
//                         <button onclick="Alpine.store('adminDashboard').showEditEmployee(${employee.employee_id}, '${employee.staff.username.replace(/'/g, "\\'")}', '${employee.department.replace(/'/g, "\\'")}', '${employee.position.replace(/'/g, "\\'")}')"
//                             class="action-btn text-blue-600 hover:text-blue-800"
//                             title="Edit">
//                             <i class="fas fa-edit"></i>
//                         </button>
//                         <button onclick="confirmDelete('employee', ${employee.employee_id})"
//                             class="action-btn text-red-600 hover:text-red-800"
//                             title="Delete">
//                             <i class="fas fa-trash"></i>
//                         </button>
//                         <a href="/admin/employees/${employee.employee_id}"
//                             class="action-btn text-green-600 hover:text-green-800"
//                             title="View">
//                             <i class="fas fa-eye"></i>
//                         </a>
//                     </div>
//                 </td>
//             </tr>
//         `;
//         tbody.append(row);
//     });
// }

// function renderArticleRows(articles, tbody) {
//     articles.forEach(article => {
//         const createdAt = new Date(article.created_at);
//         const formattedDate = createdAt.toLocaleDateString('en-US', {
//             year: 'numeric', 
//             month: 'short', 
//             day: 'numeric'
//         });

//         const statusBadge = article.is_published ? 
//             '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Published</span>' : 
//             '<span class="badge badge-warning"><i class="fas fa-pen mr-1"></i> Draft</span>';

//         const row = `
//             <tr class="hover:bg-gray-50">
//                 <td class="px-6 py-4">${article.title}</td>
//                 <td class="px-6 py-4 whitespace-nowrap">${article.author.username}</td>
//                 <td class="px-6 py-4 whitespace-nowrap">${formattedDate}</td>
//                 <td class="px-6 py-4 whitespace-nowrap">${statusBadge}</td>
//                 <td class="px-6 py-4 whitespace-nowrap">
//                     <div class="flex space-x-2">
//                         <a href="/admin/articles/${article.article_id}/edit"
//                             class="action-btn text-blue-600 hover:text-blue-800"
//                             title="Edit">
//                             <i class="fas fa-edit"></i>
//                         </a>
//                         <button onclick="confirmDelete('article', ${article.article_id})"
//                             class="action-btn text-red-600 hover:text-red-800"
//                             title="Delete">
//                             <i class="fas fa-trash"></i>
//                         </button>
//                         <a href="/admin/articles/${article.article_id}"
//                             class="action-btn text-green-600 hover:text-green-800"
//                             title="View">
//                             <i class="fas fa-eye"></i>
//                         </a>
//                         ${article.is_published ? 
//                             `<button onclick="toggleArticleStatus(${article.article_id}, false)"
//                                 class="action-btn text-yellow-600 hover:text-yellow-800"
//                                 title="Unpublish">
//                                 <i class="fas fa-eye-slash"></i>
//                             </button>` : 
//                             `<button onclick="toggleArticleStatus(${article.article_id}, true)"
//                                 class="action-btn text-purple-600 hover:text-purple-800"
//                                 title="Publish">
//                                 <i class="fas fa-upload"></i>
//                             </button>`}
//                     </div>
//                 </td>
//             </tr>
//         `;
//         tbody.append(row);
//     });
// }

// function renderCategoryRows(categories, tbody) {
//     categories.forEach(category => {
//         const createdAt = new Date(category.created_at);
//         const formattedDate = createdAt.toLocaleDateString('en-US', {
//             year: 'numeric', 
//             month: 'short', 
//             day: 'numeric'
//         });

//         const row = `
//             <tr class="hover:bg-gray-50">
//                 <td class="px-6 py-4 whitespace-nowrap">${category.name}</td>
//                 <td class="px-6 py-4 whitespace-nowrap">${category.slug}</td>
//                 <td class="px-6 py-4 whitespace-nowrap">${formattedDate}</td>
//                 <td class="px-6 py-4 whitespace-nowrap">
//                     <div class="flex space-x-2">
//                         <a href="/admin/categories/${category.category_id}/edit"
//                             class="action-btn text-blue-600 hover:text-blue-800"
//                             title="Edit">
//                             <i class="fas fa-edit"></i>
//                         </a>
//                         <button onclick="confirmDelete('category', ${category.category_id})"
//                             class="action-btn text-red-600 hover:text-red-800"
//                             title="Delete">
//                             <i class="fas fa-trash"></i>
//                         </button>
//                     </div>
//                 </td>
//             </tr>
//         `;
//         tbody.append(row);
//     });
// }

// function renderActionLogRows(logs, tbody) {
//     logs.forEach(log => {
//         const timestamp = new Date(log.created_at);
//         const formattedTime = timestamp.toLocaleString('en-US', {
//             year: 'numeric', 
//             month: 'short', 
//             day: 'numeric',
//             hour: '2-digit',
//             minute: '2-digit'
//         });

//         const row = `
//             <tr class="hover:bg-gray-50">
//                 <td class="px-6 py-4 whitespace-nowrap">${log.user.username}</td>
//                 <td class="px-6 py-4 whitespace-nowrap">${log.action_type}</td>
//                 <td class="px-6 py-4">${log.description}</td>
//                 <td class="px-6 py-4 whitespace-nowrap">${formattedTime}</td>
//             </tr>
//         `;
//         tbody.append(row);
//     });
// }

// function initializeSearch(tab) {
//     let searchTimeout;
//     $(`#${tab}Search`).on('input', function() {
//         clearTimeout(searchTimeout);
//         searchTimeout = setTimeout(() => {
//             loadTab(tab);
//         }, 500);
//     });
// }

// function updateStats(stats) {
//     if (stats.employees) $('#employeesCount').text(stats.employees);
//     if (stats.articles) $('#articlesCount').text(stats.articles);
//     if (stats.categories) $('#categoriesCount').text(stats.categories);
// }

// function showNotification(title, message, type) {
//     const Toast = Swal.mixin({
//         toast: true,
//         position: 'top-end',
//         showConfirmButton: false,
//         timer: 3000,
//         timerProgressBar: true,
//         didOpen: (toast) => {
//             toast.addEventListener('mouseenter', Swal.stopTimer)
//             toast.addEventListener('mouseleave', Swal.resumeTimer)
//         }
//     });

//     Toast.fire({
//         icon: type,
//         title: title,
//         text: message
//     });
// }

// function confirmDelete(type, id) {
//     Swal.fire({
//         title: 'Are you sure?',
//         text: `You are about to delete this ${type}. This action cannot be undone!`,
//         icon: 'warning',
//         showCancelButton: true,
//         confirmButtonColor: '#3085d6',
//         cancelButtonColor: '#d33',
//         confirmButtonText: 'Yes, delete it!'
//     }).then((result) => {
//         if (result.isConfirmed) {
//             deleteItem(type, id);
//         }
//     });
// }

// function deleteItem(type, id) {
//     $.ajax({
//         url: `/admin/${type}/${id}`,
//         type: 'DELETE',
//         success: function(response) {
//             showNotification('Success', `${type.charAt(0).toUpperCase() + type.slice(1)} deleted successfully!`, 'success');
//             loadTab(type + 's');
//         },
//         error: function(xhr) {
//             showNotification('Error', xhr.responseJSON.message || `Failed to delete ${type}`, 'error');
//         }
//     });
// }

// function toggleArticleStatus(articleId, publish) {
//     const action = publish ? 'publish' : 'unpublish';
//     const actionText = publish ? 'Publish' : 'Unpublish';
    
//     $.ajax({
//         url: `/admin/articles/${articleId}/${action}`,
//         type: 'POST',
//         success: function(response) {
//             showNotification('Success', `Article ${actionText}ed successfully!`, 'success');
//             loadTab('articles');
//         },
//         error: function(xhr) {
//             showNotification('Error', xhr.responseJSON.message || `Failed to ${action} article`, 'error');
//         }
//     });
// }

// // Handle pagination clicks
// $(document).on('click', '.pagination a', function(e) {
//     e.preventDefault();
//     const url = new URL($(this).attr('href'));
//     const tab = url.pathname.split('/')[2];
//     const page = url.searchParams.get('page');
//     loadTab(tab, page);
// });