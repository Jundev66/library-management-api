import { test, expect } from '@playwright/test';

test.describe('Library Management E2E API Adversarial Test Suite', () => {
  const timestamp = Date.now();
  let baseCategoryId: number;
  let categoryId: number;
  let bookId: number;

  test.beforeAll(async ({ request }) => {
    const res = await request.post('/api/categories', {
      data: { name: `Base_Cat_${timestamp}` },
    });
    expect(res.status()).toBe(201);
    const json = await res.json();
    baseCategoryId = json.data.id;
  });

  test('1. Nominal Flow: Category & Book CRUD with Business Invariants', async ({ request }) => {
    const categoryName = `E2E_Cat_${timestamp}`;
    const createCatRes = await request.post('/api/categories', {
      data: { name: categoryName },
    });
    expect(createCatRes.status()).toBe(201);
    const catJson = await createCatRes.json();
    categoryId = catJson.data.id;
    expect(catJson.data.name).toBe(categoryName);

    const listCatsRes = await request.get('/api/categories');
    expect(listCatsRes.status()).toBe(200);
    const listCatsJson = await listCatsRes.json();
    expect(listCatsJson.data.some((c: any) => c.id === categoryId)).toBeTruthy();

    const createBookRes = await request.post('/api/books', {
      data: {
        title: `Book_${timestamp}`,
        author: 'E2E Test Author',
        description: 'E2E generated description for full integration testing.',
        published_year: 2024,
        category_id: categoryId,
      },
    });
    expect(createBookRes.status()).toBe(201);
    const bookJson = await createBookRes.json();
    bookId = bookJson.data.id;
    expect(bookJson.data.title).toBe(`Book_${timestamp}`);
    expect(bookJson.data.category.id).toBe(categoryId);

    const listBooksRes = await request.get('/api/books');
    expect(listBooksRes.status()).toBe(200);
    const listBooksJson = await listBooksRes.json();
    expect(listBooksJson.data.some((b: any) => b.id === bookId)).toBeTruthy();

    const filterRes = await request.get(`/api/books?category=${categoryId}`);
    expect(filterRes.status()).toBe(200);
    const filterJson = await filterRes.json();
    expect(filterJson.data.length).toBeGreaterThanOrEqual(1);
    expect(filterJson.data.every((b: any) => b.category_id === categoryId)).toBeTruthy();

    const updateBookRes = await request.put(`/api/books/${bookId}`, {
      data: {
        title: `Updated_Book_${timestamp}`,
        author: 'E2E Updated Author',
        description: 'Updated description.',
        published_year: 2025,
        category_id: categoryId,
      },
    });
    expect(updateBookRes.status()).toBe(200);
    const updateJson = await updateBookRes.json();
    expect(updateJson.data.title).toBe(`Updated_Book_${timestamp}`);

    const deleteCatConflictRes = await request.delete(`/api/categories/${categoryId}`);
    expect(deleteCatConflictRes.status()).toBe(409);
    const conflictJson = await deleteCatConflictRes.json();
    expect(conflictJson.message).toBe('Cannot delete category because it has associated books.');

    const deleteBookRes = await request.delete(`/api/books/${bookId}`);
    expect(deleteBookRes.status()).toBe(204);

    const deleteBookAgainRes = await request.delete(`/api/books/${bookId}`);
    expect(deleteBookAgainRes.status()).toBe(404);
    const notFoundJson = await deleteBookAgainRes.json();
    expect(notFoundJson.message).toBe('Resource not found.');

    const deleteCatSuccessRes = await request.delete(`/api/categories/${categoryId}`);
    expect(deleteCatSuccessRes.status()).toBe(204);

    const deleteCatAgainRes = await request.delete(`/api/categories/${categoryId}`);
    expect(deleteCatAgainRes.status()).toBe(404);
  });

  test('2. Boundary & Validation Stress Testing', async ({ request }) => {
    const shortTitleRes = await request.post('/api/books', {
      data: {
        title: 'Ab',
        author: 'Author',
        published_year: 2024,
        category_id: baseCategoryId,
      },
    });
    expect(shortTitleRes.status()).toBe(422);
    const shortTitleJson = await shortTitleRes.json();
    expect(shortTitleJson.errors.title).toBeDefined();

    const utf8Res = await request.post('/api/books', {
      data: {
        title: 'Cien años de magia 📖✨',
        author: 'Gabriel García Márquez',
        published_year: 1967,
        category_id: baseCategoryId,
      },
    });
    expect(utf8Res.status()).toBe(201);
    const utf8Json = await utf8Res.json();
    expect(utf8Json.data.title).toBe('Cien años de magia 📖✨');

    await request.delete(`/api/books/${utf8Json.data.id}`);

    const ancientYearRes = await request.post('/api/books', {
      data: {
        title: 'Historia Antigua',
        author: 'Autor',
        published_year: 999,
        category_id: baseCategoryId,
      },
    });
    expect(ancientYearRes.status()).toBe(422);

    const nonExistentCatRes = await request.post('/api/books', {
      data: {
        title: 'Libro Huerfano',
        author: 'Autor',
        published_year: 2024,
        category_id: 99999999,
      },
    });
    expect(nonExistentCatRes.status()).toBe(422);

    const invalidFilterRes = await request.get('/api/books?category=not_a_number');
    expect(invalidFilterRes.status()).toBe(422);

    const emptyFilterRes = await request.get('/api/books?category=99999999');
    expect(emptyFilterRes.status()).toBe(200);
    const emptyJson = await emptyFilterRes.json();
    expect(emptyJson.data).toEqual([]);
  });

  test('3. Concurrency & Race Condition Stress: Parallel Duplicate Protection', async ({ request }) => {
    const uniqueName = `ConcurrentCat_${Date.now()}`;

    const [res1, res2] = await Promise.all([
      request.post('/api/categories', { data: { name: uniqueName } }),
      request.post('/api/categories', { data: { name: uniqueName } }),
    ]);

    const statuses = [res1.status(), res2.status()].sort();
    expect(statuses).toEqual([201, 422]);

    const createdCat = (await res1.json())?.data?.id || (await res2.json())?.data?.id;
    if (createdCat) {
      await request.delete(`/api/categories/${createdCat}`);
    }
  });

  test('4. Concurrency Stress: Parallel Duplicate Deletion', async ({ request }) => {
    const tempBookRes = await request.post('/api/books', {
      data: {
        title: `Temp_Book_${Date.now()}`,
        author: 'Temp Author',
        published_year: 2024,
        category_id: baseCategoryId,
      },
    });
    expect(tempBookRes.status()).toBe(201);
    const tempBookId = (await tempBookRes.json()).data.id;

    const [del1, del2, del3] = await Promise.all([
      request.delete(`/api/books/${tempBookId}`),
      request.delete(`/api/books/${tempBookId}`),
      request.delete(`/api/books/${tempBookId}`),
    ]);

    const delStatuses = [del1.status(), del2.status(), del3.status()].sort();
    expect(delStatuses).toEqual([204, 404, 404]);
  });
});
