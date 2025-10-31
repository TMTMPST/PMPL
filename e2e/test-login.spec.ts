import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost:8000';
const API_URL = `${BASE_URL}/api`;

test('Test Login API', async ({ request }) => {
  console.log('Testing login at:', `${API_URL}/login`);
  
  const response = await request.post(`${API_URL}/login`, {
    data: {
      username: 'admin',
      password: 'sipasti123'
    }
  });
  
  console.log('Response status:', response.status());
  console.log('Response headers:', await response.headersArray());
  
  const responseData = await response.json();
  console.log('Response body:', JSON.stringify(responseData, null, 2));
  
  expect(response.ok()).toBeTruthy();
  expect(responseData.success).toBe(true);
  expect(responseData).toHaveProperty('access_token');
});
