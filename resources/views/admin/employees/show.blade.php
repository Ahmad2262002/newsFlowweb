<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Employee Details</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap & Font Awesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f5f7fa;
    }

    .card {
      border-radius: 1rem;
      box-shadow: 0 0 30px rgba(0, 0, 0, 0.05);
    }

    .card-header {
      border-radius: 1rem 1rem 0 0;
      background-color: #4e73df;
      color: #fff;
    }

    .employee-details {
      background-color: #f8f9fa;
      border-radius: 0.75rem;
      padding: 24px;
    }

    .detail-item {
      display: flex;
      align-items: center;
      padding: 16px 0;
      border-bottom: 1px solid #e9ecef;
    }

    .detail-item:last-child {
      border-bottom: none;
    }

    .detail-label {
      flex: 0 0 180px;
      font-weight: 600;
      color: #6c757d;
    }

    .detail-value {
      flex: 1;
      font-size: 1.1rem;
      color: #343a40;
    }

    .btn-primary {
      background-color: #4e73df;
      border-color: #4e73df;
    }

    .btn-primary:hover {
      background-color: #3a5ec0;
      border-color: #3a5ec0;
    }

    .btn-outline-secondary:hover {
      background-color: #f0f0f0;
    }

    @media (max-width: 576px) {
      .detail-label {
        flex: 0 0 120px;
        font-size: 0.9rem;
      }

      .detail-value {
        font-size: 1rem;
      }
    }
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-10">
        <div class="card">
          <div class="card-header p-4">
            <div class="d-flex justify-content-between align-items-center">
              <h4 class="mb-0"><i class="fas fa-id-badge me-2"></i>Employee Details</h4>
              
            </div>
          </div>
          <div class="card-body p-4">
            <div class="employee-details">
              <div class="detail-item">
                <div class="detail-label">Full Name</div>
                <div class="detail-value">
                  <i class="fas fa-user me-2 text-primary"></i>
                  {{ $employee->staff->username }}
                </div>
              </div>

              <div class="detail-item">
                <div class="detail-label">Email Address</div>
                <div class="detail-value">
                  <i class="fas fa-envelope me-2 text-primary"></i>
                  
                  <a href="mailto:{{ $employee->staff->email }}">{{ $employee->staff->email }}</a>
                </div>
              </div>

              <div class="detail-item">
                <div class="detail-label">Position</div>
                <div class="detail-value">
                  <i class="fas fa-briefcase me-2 text-primary"></i>{{ $employee->position }}
                 
                </div>
              </div>

              <div class="detail-item">
                <div class="detail-label">Hire Date</div>
                <div class="detail-value">
                  <i class="fas fa-calendar-alt me-2 text-primary"></i> {{ \Carbon\Carbon::parse($employee->hire_date)->format('F j, Y') }}
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end mt-4 gap-2">
              <a href="{{ route('admin.employees.edit', $employee->employee_id) }}" class="btn btn-primary px-4">
                <i class="fas fa-edit me-1"></i> Edit Profile
              </a>
              <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary px-4">
                <i class="fas fa-tachometer-alt me-1"></i> Dashboard
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
