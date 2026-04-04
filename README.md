# Student Co-curricular Management System + even tracker (UCCD3243)

---
    * `header.php` - I MODIFIED THIS SRY
    * `sidebar.php` - I MODIFIED THIS SRY

---

## How to Use GitHub

Follow these steps every time you work to avoid errors!

### 1. Start of the Day
Always pull the latest code from the team before you start typing.
Commands : 
* git pull origin main

When you want to make changes : 
* git add .
* git commit -m "Briefly describe what you did"
* git push origin main

Check status : 
* git status

### How to Create a New Module Page

* Each member is responsible for one module (Event, Club, Merit, or Achievement). To make your page look professional and match the rest of the site, use the following PHP structure:

```
<?php
session_start();
// Access Control: Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include('includes/header.php'); 
include('includes/sidebar.php'); 
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Your Module Name</h1>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data List</h6>
        </div>
        <div class="card-body">
            </div>
    </div>
</div>
<?php include('includes/footer.php'); ?>
```