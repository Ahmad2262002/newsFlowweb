document.addEventListener('DOMContentLoaded', function() {
    // Initialize CSRF token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    // Tab management
    $('.dashboard-tab').on('click', function(e) {
        e.preventDefault();
        const tab = $(this).data('tab');
        loadTab(tab);
    });

    // Load initial tab
    loadTab('employees');
});

function loadTab(tab, page = 1) {
    // Show loading state
    $(`#${tab}Table tbody`).html('<tr><td colspan="5" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');
    
    // Update active tab UI
    $('.dashboard-tab').removeClass('active');
    $(`.dashboard-tab[data-tab="${tab}"]`).addClass('active');

    let url = `/admin/${tab}/data?page=${page}`;
    const search = $(`#${tab}Search`).val();
    
    // Add search filters if they exist
    if (search) {
        url += `&search=${encodeURIComponent(search)}`;
    }

    // Special filters
    if (tab === 'articles') {
        const status = $('#articleStatusFilter').val();
        if (status) url += `&status=${status}`;
    } else if (tab === 'action-logs') {
        const dateRange = $('#dateRangePicker').val();
        if (dateRange) {
            const dates = dateRange.split(' - ');
            url += `&date_from=${dates[0]}&date_to=${dates[1]}`;
        }
    }

    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            renderTable(tab, response);
        },
        error: function(xhr) {
            $(`#${tab}Table tbody`).html('<tr><td colspan="5" class="text-center py-4 text-red-500">Failed to load data</td></tr>');
            console.error(`Error loading ${tab}:`, xhr.responseText);
        }
    });
}

function renderTable(tab, data) {
    const tbody = $(`#${tab}Table tbody`);
    tbody.empty();

    if (data.data.length === 0) {
        tbody.html('<tr><td colspan="5" class="text-center py-4">No records found</td></tr>');
        return;
    }

    // Render rows based on tab type
    switch(tab) {
        case 'employees':
            renderEmployeeRows(data.data, tbody);
            break;
        case 'articles':
            renderArticleRows(data.data, tbody);
            break;
        case 'categories':
            renderCategoryRows(data.data, tbody);
            break;
        case 'action-logs':
            renderActionLogRows(data.data, tbody);
            break;
    }

    // Update pagination
    $(`#${tab}Pagination`).html(data.links);
}

// Individual row rendering functions would go here...
// Example for employees:
function renderEmployeeRows(employees, tbody) {
    employees.forEach(employee => {
        const hireDate = new Date(employee.hire_date);
        const formattedHireDate = hireDate.toLocaleDateString('en-US', {
            year: 'numeric', 
            month: 'short', 
            day: 'numeric'
        });

        const row = `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">${employee.staff.username}</td>
                <td class="px-6 py-4 whitespace-nowrap">${employee.staff.email}</td>
                <td class="px-6 py-4 whitespace-nowrap">${employee.position}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formattedHireDate}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex space-x-2">
                        <a href="/admin/employees/${employee.employee_id}/edit"
                            class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button onclick="confirmDelete('employee', ${employee.employee_id})"
                            class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

// Global delete function
function confirmDelete(type, id) {
    if (confirm(`Are you sure you want to delete this ${type}?`)) {
        $.ajax({
            url: `/admin/${type}s/${id}`,
            type: 'DELETE',
            success: function() {
                // Reload the current tab
                const activeTab = $('.dashboard-tab.active').data('tab');
                loadTab(activeTab);
                
                // Show success message
                alert(`${type.charAt(0).toUpperCase() + type.slice(1)} deleted successfully`);
            },
            error: function(xhr) {
                console.error(`Error deleting ${type}:`, xhr.responseText);
                alert(`Failed to delete ${type}`);
            }
        });
    }
}

// Initialize search with debounce
function initializeSearch(tab) {
    const searchInput = $(`#${tab}Search`);
    let timeout = null;
    
    searchInput.on('keyup', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            loadTab(tab);
        }, 500);
    });
}

// Initialize filters
$('#articleStatusFilter').change(function() {
    loadTab('articles');
});

$('#filterLogsBtn').click(function() {
    loadTab('action-logs');
});

$('#resetLogsFilterBtn').click(function() {
    $('#dateRangePicker').val('');
    loadTab('action-logs');
});