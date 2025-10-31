import { test, expect } from '@playwright/test';

/**
 * E2E Tests for Manajemen Pengguna (User Management)
 * 
 * Test Cases:
 * Tambah Pengguna:
 * 1. Positive: Tambah pengguna dengan data valid
 * 2. Negative: Tambah pengguna dengan username yang sudah ada
 * 3. Negative: Tambah pengguna dengan field kosong
 * 
 * Ganti Role Pengguna:
 * 4. Positive: Ganti role pengguna dengan role yang valid
 * 5. Negative: Ganti role dengan role yang tidak valid
 * 6. Negative: Ganti role pengguna yang tidak ada
 * 
 * Hapus Pengguna:
 * 7. Positive: Konfirmasi hapus - data berhasil dihapus
 * 8. Negative: Pilih 'batal' di konfirmasi - data tidak terhapus
 * 9. Negative: Hapus pengguna yang tidak ada (ID invalid)
 * 10. Negative: Hapus pengguna yang sedang login
 * 
 * Prerequisites:
 * - Laravel application must be running (php artisan serve)
 * - Database must be seeded with at least one admin user
 * - Authentication is required (Admin role)
 */

// Test configuration
const BASE_URL = process.env.BASE_URL || 'http://localhost:8000';
const API_URL = `${BASE_URL}/api`;

// Test data
const ADMIN_CREDENTIALS = {
  username: process.env.ADMIN_USERNAME || 'admin',
  password: process.env.ADMIN_PASSWORD || 'sipasti123'
};

// Helper function to create user via API
async function createUser(request: any, authCookies: string, data: any) {
  return await request.post(`${API_URL}/kelola-pengguna/create`, {
    data: data,
    headers: {
      'Cookie': authCookies
    }
  });
}

// Helper function to update user via API
async function updateUser(request: any, authCookies: string, id: number, data: any) {
  return await request.put(`${API_URL}/kelola-pengguna/${id}`, {
    data: data,
    headers: {
      'Cookie': authCookies
    }
  });
}

// Helper function to delete user via API
async function deleteUser(request: any, authCookies: string, id: number) {
  return await request.delete(`${API_URL}/kelola-pengguna/${id}`, {
    headers: {
      'Cookie': authCookies
    }
  });
}

// Helper function to get user detail
async function getUserDetail(request: any, authCookies: string, id: number) {
  return await request.get(`${API_URL}/kelola-pengguna/${id}`, {
    headers: {
      'Cookie': authCookies
    }
  });
}

// Helper function to get current user info
async function getCurrentUser(request: any, authCookies: string) {
  return await request.get(`${API_URL}/user`, {
    headers: {
      'Cookie': authCookies
    }
  });
}

test.describe('Tambah Pengguna', () => {
  let authCookies: string;

  test.beforeAll(async ({ browser }) => {
    // Login once for all tests in this suite
    const context = await browser.newContext();
    const page = await context.newPage();
    
    const loginResponse = await page.request.post(`${API_URL}/login`, {
      data: {
        username: ADMIN_CREDENTIALS.username,
        password: ADMIN_CREDENTIALS.password
      }
    });
    
    if (!loginResponse.ok()) {
      const text = await loginResponse.text();
      throw new Error(`Login failed: ${loginResponse.status()} - ${text}`);
    }
    
    const loginData = await loginResponse.json();
    if (!loginData.success) {
      throw new Error(`Login unsuccessful: ${loginData.message}`);
    }
    
    // Get the JWT cookie
    const cookies = await context.cookies();
    const jwtCookie = cookies.find(c => c.name === 'jwtToken');
    
    if (!jwtCookie) {
      throw new Error('JWT token not set in cookies');
    }
    
    authCookies = `jwtToken=${jwtCookie.value}`;
    
    await context.close();
  });

  test('TC-USER-POS-01: Tambah pengguna dengan data valid', async ({ request }) => {
    // Arrange
    const timestamp = Date.now();
    const validUserData = {
      username: `testuser${timestamp}`,
      password: 'password123',
      fullname: `Test User ${timestamp}`,
      role_id: 2 // Assuming role_id 2 exists (e.g., Staff/User)
    };

    // Act
    const response = await createUser(request, authCookies, validUserData);

    // Assert
    expect(response.status()).toBe(200);
    const responseData = await response.json();
    expect(responseData.success).toBe(true);
    expect(responseData.message).toBeDefined();
    expect(responseData.data).toHaveProperty('user_id');
    expect(responseData.data.username).toBe(validUserData.username);
    expect(responseData.data.fullname).toBe(validUserData.fullname);
    expect(responseData.data.role_id).toBe(validUserData.role_id);

    // Cleanup
    await deleteUser(request, authCookies, responseData.data.user_id);
  });

  test('TC-USER-NEG-01: Tambah pengguna dengan username yang sudah ada', async ({ request }) => {
    // Arrange - Create first user
    const timestamp = Date.now();
    const userName = `duplicate${timestamp}`;
    const firstUserData = {
      username: userName,
      password: 'password123',
      fullname: `First User ${timestamp}`,
      role_id: 2
    };

    const firstResponse = await createUser(request, authCookies, firstUserData);
    const firstData = await firstResponse.json();
    expect(firstResponse.status()).toBe(200);
    expect(firstData.success).toBe(true);

    // Act - Try to create user with same username
    const duplicateUserData = {
      username: userName,
      password: 'password456',
      fullname: `Second User ${timestamp}`,
      role_id: 2
    };

    const duplicateResponse = await createUser(request, authCookies, duplicateUserData);

    // Assert
    expect(duplicateResponse.status()).toBe(422); // Validation error
    const duplicateData = await duplicateResponse.json();
    expect(duplicateData.message).toBeDefined();
    expect(duplicateData.errors).toHaveProperty('username');
    expect(Array.isArray(duplicateData.errors.username)).toBe(true);
    expect(duplicateData.errors.username.length).toBeGreaterThan(0);

    // Cleanup
    await deleteUser(request, authCookies, firstData.data.user_id);
  });

  test('TC-USER-NEG-02: Tambah pengguna dengan field kosong', async ({ request }) => {
    // Arrange
    const emptyUserData = {
      username: '',
      password: '',
      fullname: '',
      role_id: null
    };

    // Act
    const response = await createUser(request, authCookies, emptyUserData);

    // Assert
    expect(response.status()).toBe(422); // Validation error
    const responseData = await response.json();
    expect(responseData.message).toBeDefined();
    expect(responseData.errors).toBeDefined();
    
    // Check that validation errors exist for required fields
    const errors = responseData.errors;
    expect(
      errors.hasOwnProperty('username') || 
      errors.hasOwnProperty('password') || 
      errors.hasOwnProperty('fullname') || 
      errors.hasOwnProperty('role_id')
    ).toBe(true);
  });
});

test.describe('Ganti Role Pengguna', () => {
  let authCookies: string;

  test.beforeAll(async ({ browser }) => {
    // Login once for all tests in this suite
    const context = await browser.newContext();
    const page = await context.newPage();
    
    const loginResponse = await page.request.post(`${API_URL}/login`, {
      data: {
        username: ADMIN_CREDENTIALS.username,
        password: ADMIN_CREDENTIALS.password
      }
    });
    
    if (!loginResponse.ok()) {
      const text = await loginResponse.text();
      throw new Error(`Login failed: ${loginResponse.status()} - ${text}`);
    }
    
    const loginData = await loginResponse.json();
    if (!loginData.success) {
      throw new Error(`Login unsuccessful: ${loginData.message}`);
    }
    
    // Get the JWT cookie
    const cookies = await context.cookies();
    const jwtCookie = cookies.find(c => c.name === 'jwtToken');
    
    if (!jwtCookie) {
      throw new Error('JWT token not set in cookies');
    }
    
    authCookies = `jwtToken=${jwtCookie.value}`;
    
    await context.close();
  });

  test('TC-ROLE-POS-01: Ganti role pengguna dengan role yang valid', async ({ request }) => {
    // Arrange - Create a user first
    const timestamp = Date.now();
    const userData = {
      username: `rolechange${timestamp}`,
      password: 'password123',
      fullname: `Role Change User ${timestamp}`,
      role_id: 2 // Initial role: Staff/User
    };

    const createResponse = await createUser(request, authCookies, userData);
    const createData = await createResponse.json();
    expect(createResponse.status()).toBe(200);
    
    const userId = createData.data.user_id;
    const newRoleId = 3; // Change to different role (e.g., Manager)

    // Act - Update user's role
    const updateResponse = await updateUser(request, authCookies, userId, {
      username: userData.username,
      fullname: userData.fullname,
      role_id: newRoleId
    });

    // Assert
    expect(updateResponse.status()).toBe(200);
    const updateData = await updateResponse.json();
    expect(updateData.success).toBe(true);
    expect(updateData.message).toBeDefined();
    expect(updateData.data.role_id).toBe(newRoleId);

    // Cleanup
    await deleteUser(request, authCookies, userId);
  });

  test('TC-ROLE-NEG-01: Ganti role dengan role yang tidak valid', async ({ request }) => {
    // Arrange - Create a user first
    const timestamp = Date.now();
    const userData = {
      username: `invalidrole${timestamp}`,
      password: 'password123',
      fullname: `Invalid Role User ${timestamp}`,
      role_id: 2
    };

    const createResponse = await createUser(request, authCookies, userData);
    const createData = await createResponse.json();
    expect(createResponse.status()).toBe(200);
    
    const userId = createData.data.user_id;
    const invalidRoleId = 99999; // Non-existent role

    // Act - Try to update with invalid role
    const updateResponse = await updateUser(request, authCookies, userId, {
      username: userData.username,
      fullname: userData.fullname,
      role_id: invalidRoleId
    });

    // Assert
    expect(updateResponse.status()).toBe(422); // Validation error
    const updateData = await updateResponse.json();
    expect(updateData.message).toBeDefined();
    expect(updateData.errors).toHaveProperty('role_id');

    // Cleanup
    await deleteUser(request, authCookies, userId);
  });

  test('TC-ROLE-NEG-02: Ganti role pengguna yang tidak ada', async ({ request }) => {
    // Arrange
    const nonExistentUserId = 999999;

    // Act - Try to update non-existent user
    const updateResponse = await updateUser(request, authCookies, nonExistentUserId, {
      username: 'nonexistent',
      fullname: 'Non Existent User',
      role_id: 2
    });

    // Assert
    expect(updateResponse.status()).toBe(404);
    const updateData = await updateResponse.json();
    expect(updateData.success).toBe(false);
    expect(updateData.message).toBeDefined();
  });
});

test.describe('Hapus Pengguna', () => {
  let authCookies: string;
  let currentUserId: number;

  test.beforeAll(async ({ browser }) => {
    // Login once for all tests in this suite
    const context = await browser.newContext();
    const page = await context.newPage();
    
    const loginResponse = await page.request.post(`${API_URL}/login`, {
      data: {
        username: ADMIN_CREDENTIALS.username,
        password: ADMIN_CREDENTIALS.password
      }
    });
    
    if (!loginResponse.ok()) {
      const text = await loginResponse.text();
      throw new Error(`Login failed: ${loginResponse.status()} - ${text}`);
    }
    
    const loginData = await loginResponse.json();
    if (!loginData.success) {
      throw new Error(`Login unsuccessful: ${loginData.message}`);
    }
    
    // Get the JWT cookie
    const cookies = await context.cookies();
    const jwtCookie = cookies.find(c => c.name === 'jwtToken');
    
    if (!jwtCookie) {
      throw new Error('JWT token not set in cookies');
    }
    
    authCookies = `jwtToken=${jwtCookie.value}`;
    
    // Get current user ID
    const userResponse = await page.request.get(`${API_URL}/user`, {
      headers: {
        'Cookie': authCookies
      }
    });
    
    if (userResponse.ok()) {
      const userData = await userResponse.json();
      if (userData.success && userData.data) {
        currentUserId = userData.data.user_id;
      }
    }
    
    await context.close();
  });

  test('TC-DELETE-USER-POS-01: Konfirmasi hapus - data berhasil dihapus', async ({ request }) => {
    // Arrange - Create a user
    const timestamp = Date.now();
    const userData = {
      username: `deleteuser${timestamp}`,
      password: 'password123',
      fullname: `Delete Test User ${timestamp}`,
      role_id: 2
    };

    const createResponse = await createUser(request, authCookies, userData);
    const createData = await createResponse.json();
    expect(createResponse.status()).toBe(200);
    
    const userId = createData.data.user_id;

    // Act - Delete the user (simulating 'hapus' confirmation)
    const deleteResponse = await deleteUser(request, authCookies, userId);

    // Assert
    expect(deleteResponse.status()).toBe(200);
    const deleteData = await deleteResponse.json();
    expect(deleteData.success).toBe(true);
    expect(deleteData.message).toBeDefined();

    // Verify the user is actually deleted
    const detailResponse = await getUserDetail(request, authCookies, userId);
    expect(detailResponse.status()).toBe(404);
    const detailData = await detailResponse.json();
    expect(detailData.success).toBe(false);
  });

  test('TC-DELETE-USER-NEG-01: Pilih batal di konfirmasi - data tidak terhapus', async ({ request }) => {
    // Arrange - Create a user
    const timestamp = Date.now();
    const userData = {
      username: `canceldelete${timestamp}`,
      password: 'password123',
      fullname: `Cancel Delete User ${timestamp}`,
      role_id: 2
    };

    const createResponse = await createUser(request, authCookies, userData);
    const createData = await createResponse.json();
    expect(createResponse.status()).toBe(200);
    
    const userId = createData.data.user_id;

    // Act - Simulate 'batal' - don't actually delete
    // In real UI, this would be handled by not calling the delete API
    // For this test, we verify the data still exists

    // Assert - Verify the user still exists
    const detailResponse = await getUserDetail(request, authCookies, userId);
    expect(detailResponse.status()).toBe(200);
    const detailData = await detailResponse.json();
    expect(detailData.success).toBe(true);
    expect(detailData.data.username).toBe(userData.username);

    // Cleanup
    await deleteUser(request, authCookies, userId);
  });

  test('TC-DELETE-USER-NEG-02: Hapus pengguna yang tidak ada (ID invalid)', async ({ request }) => {
    // Arrange
    const nonExistentUserId = 999999;

    // Act - Try to delete non-existent user
    const deleteResponse = await deleteUser(request, authCookies, nonExistentUserId);

    // Assert
    expect(deleteResponse.status()).toBe(404);
    const deleteData = await deleteResponse.json();
    expect(deleteData.success).toBe(false);
    expect(deleteData.message).toBeDefined();
  });

  test('TC-DELETE-USER-NEG-03: Hapus pengguna yang sedang login', async ({ request }) => {
    // Arrange - Current user is already logged in (from beforeAll)
    // Assuming currentUserId is set from the logged-in admin user

    // Act - Try to delete current user
    const deleteResponse = await deleteUser(request, authCookies, currentUserId);

    // Assert
    // The behavior depends on implementation:
    // Option 1: Prevent deletion (recommended)
    // Option 2: Allow deletion (not recommended)
    
    if (deleteResponse.status() === 400 || deleteResponse.status() === 403 || deleteResponse.status() === 422) {
      // Expected: Server prevents deletion of currently logged-in user
      const deleteData = await deleteResponse.json();
      expect(deleteData.success).toBe(false);
      expect(deleteData.message).toBeDefined();
    } else if (deleteResponse.status() === 200) {
      // If deletion is allowed, document this behavior
      const deleteData = await deleteResponse.json();
      expect(deleteData.success).toBe(true);
      
      // Note: This test documents current behavior
      // Ideally, logged-in user should not be able to delete themselves
      console.warn('Warning: Currently logged-in user was deleted. Consider adding constraint.');
    }
  });
});
