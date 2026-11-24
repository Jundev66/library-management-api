import { test, expect } from '@playwright/test';

test.describe('Senior QA Adversarial & Penetration Stress Suite', () => {
  const currentYear = new Date().getFullYear();
  let validCategoryId: number;

  test.beforeAll(async ({ request }) => {
    const res = await request.post('/api/categories', {
      data: { name: `QA_Stress_Cat_${Date.now()}` },
    });
    expect(res.status()).toBe(201);
    const json = await res.json();
    validCategoryId = json.data.id;
  });

  test('Vector 1: SQL Injection & Array Parameter Tampering', async ({ request }) => {
    const sqlInjectionFilter = await request.get(`/api/books?category=1'%20OR%20'1'='1`);
    expect(sqlInjectionFilter.status()).toBe(422);

    const arrayTamperingFilter = await request.get(`/api/books?category[]=1&category[]=2`);
    expect(arrayTamperingFilter.status()).toBe(422);

    const sqlInjectionPayload = {
      title: `'; DROP TABLE books; --`,
      author: `Robert'); DROP TABLE categories;--`,
      description: `Normal text`,
      published_year: 2024,
      category_id: validCategoryId,
    };
    const createBookRes = await request.post('/api/books', { data: sqlInjectionPayload });
    expect(createBookRes.status()).toBe(201);
    const bookJson = await createBookRes.json();
    expect(bookJson.data.title).toBe(`'; DROP TABLE books; --`);

    const tableStillExists = await request.get('/api/books');
    expect(tableStillExists.status()).toBe(200);

    await request.delete(`/api/books/${bookJson.data.id}`);
  });

  test('Vector 2: XSS & HTML Script Tag Injections in Strings', async ({ request }) => {
    const xssPayload = {
      title: `<script>alert('XSS')</script>`,
      author: `<img src=x onerror=alert(1)>`,
      description: `<b onmouseover=alert('hack')>Bold</b>`,
      published_year: 2024,
      category_id: validCategoryId,
    };
    const res = await request.post('/api/books', { data: xssPayload });
    expect(res.status()).toBe(201);
    const json = await res.json();
    expect(json.data.title).toBe(`<script>alert('XSS')</script>`);
    expect(res.headers()['content-type']).toContain('application/json');

    await request.delete(`/api/books/${json.data.id}`);
  });

  test('Vector 3: Type Juggling, Booleans & Floating Points', async ({ request }) => {
    const emptyJsonRes = await request.post('/api/books', { data: {} });
    expect(emptyJsonRes.status()).toBe(422);
    const emptyErrors = (await emptyJsonRes.json()).errors;
    expect(emptyErrors.title).toBeDefined();
    expect(emptyErrors.author).toBeDefined();
    expect(emptyErrors.published_year).toBeDefined();
    expect(emptyErrors.category_id).toBeDefined();

    const boolRes = await request.post('/api/books', {
      data: {
        title: true,
        author: false,
        published_year: true,
        category_id: false,
      },
    });
    expect(boolRes.status()).toBe(422);

    const floatRes = await request.post('/api/books', {
      data: {
        title: 'Libro Flotante',
        author: 'Autor',
        published_year: 2024.75,
        category_id: validCategoryId,
      },
    });
    expect(floatRes.status()).toBe(422);

    const floatCatRes = await request.post('/api/books', {
      data: {
        title: 'Libro Categoria Flotante',
        author: 'Autor',
        published_year: 2024,
        category_id: 1.5,
      },
    });
    expect(floatCatRes.status()).toBe(422);
  });

  test('Vector 4: Boundary Limits (Min, Max, Years)', async ({ request }) => {
    const title2Chars = await request.post('/api/books', {
      data: {
        title: '12',
        author: 'Author',
        published_year: 2024,
        category_id: validCategoryId,
      },
    });
    expect(title2Chars.status()).toBe(422);

    const title3Chars = await request.post('/api/books', {
      data: {
        title: '123',
        author: 'Author',
        published_year: 2024,
        category_id: validCategoryId,
      },
    });
    expect(title3Chars.status()).toBe(201);
    const book3 = await title3Chars.json();
    await request.delete(`/api/books/${book3.data.id}`);

    const title255 = 'A'.repeat(255);
    const res255 = await request.post('/api/books', {
      data: {
        title: title255,
        author: 'Author',
        published_year: 2024,
        category_id: validCategoryId,
      },
    });
    expect(res255.status()).toBe(201);
    const book255 = await res255.json();
    await request.delete(`/api/books/${book255.data.id}`);

    const title256 = 'A'.repeat(256);
    const res256 = await request.post('/api/books', {
      data: {
        title: title256,
        author: 'Author',
        published_year: 2024,
        category_id: validCategoryId,
      },
    });
    expect(res256.status()).toBe(422);

    const year999 = await request.post('/api/books', {
      data: {
        title: 'Año 999',
        author: 'Author',
        published_year: 999,
        category_id: validCategoryId,
      },
    });
    expect(year999.status()).toBe(422);

    const year1000 = await request.post('/api/books', {
      data: {
        title: 'Año 1000',
        author: 'Author',
        published_year: 1000,
        category_id: validCategoryId,
      },
    });
    expect(year1000.status()).toBe(201);
    const book1000 = await year1000.json();
    await request.delete(`/api/books/${book1000.data.id}`);

    const nextYear = currentYear + 1;
    const resNextYear = await request.post('/api/books', {
      data: {
        title: 'Libro Año Proximo',
        author: 'Author',
        published_year: nextYear,
        category_id: validCategoryId,
      },
    });
    expect(resNextYear.status()).toBe(201);
    const bookNext = await resNextYear.json();
    await request.delete(`/api/books/${bookNext.data.id}`);

    const tooFarFuture = currentYear + 2;
    const resFarFuture = await request.post('/api/books', {
      data: {
        title: 'Libro Año Lejano',
        author: 'Author',
        published_year: tooFarFuture,
        category_id: validCategoryId,
      },
    });
    expect(resFarFuture.status()).toBe(422);
  });

  test('Vector 5: Negative & Non-Numeric IDs, Overflow and Method Confusion', async ({ request }) => {
    const deleteNonNumeric = await request.delete('/api/books/abc');
    expect(deleteNonNumeric.status()).toBe(404);

    const nonNumericBookRes = await request.get('/api/books/abc');
    expect([404, 405]).toContain(nonNumericBookRes.status());

    const negativeBookRes = await request.delete('/api/books/-1');
    expect(negativeBookRes.status()).toBe(404);

    const zeroBookRes = await request.delete('/api/books/0');
    expect(zeroBookRes.status()).toBe(404);

    const overflowBookRes = await request.delete('/api/books/99999999999999999999999999');
    expect(overflowBookRes.status()).toBe(404);

    const postToDetailRes = await request.post('/api/books/1', { data: {} });
    expect([404, 405]).toContain(postToDetailRes.status());
  });

  test('Vector 6: Whitespace Edge Cases & Category Duplicate Sanitization', async ({ request }) => {
    const whitespaceTitleRes = await request.post('/api/books', {
      data: {
        title: '   ',
        author: 'Author',
        published_year: 2024,
        category_id: validCategoryId,
      },
    });
    expect(whitespaceTitleRes.status()).toBe(422);

    const paddedCatName = `Cat_${Date.now()}`;
    const createPaddedRes = await request.post('/api/categories', {
      data: { name: `  ${paddedCatName}  ` },
    });
    expect(createPaddedRes.status()).toBe(201);
    const paddedJson = await createPaddedRes.json();
    expect(paddedJson.data.name).toBe(paddedCatName);

    const createDupRes = await request.post('/api/categories', {
      data: { name: paddedCatName },
    });
    expect(createDupRes.status()).toBe(422);

    await request.delete(`/api/categories/${paddedJson.data.id}`);
  });

  test('Vector 7: Integrity Trap - Foreign Key Swap on Update', async ({ request }) => {
    const bookRes = await request.post('/api/books', {
      data: {
        title: 'Libro Base Para Update',
        author: 'Autor',
        published_year: 2024,
        category_id: validCategoryId,
      },
    });
    expect(bookRes.status()).toBe(201);
    const bookId = (await bookRes.json()).data.id;

    const invalidCatUpdate = await request.put(`/api/books/${bookId}`, {
      data: {
        title: 'Libro Con Categoria Fantasma',
        author: 'Autor',
        published_year: 2024,
        category_id: 999999999,
      },
    });
    expect(invalidCatUpdate.status()).toBe(422);

    const bookCheck = await request.get('/api/books');
    const bookList = (await bookCheck.json()).data;
    const updatedBook = bookList.find((b: any) => b.id === bookId);
    expect(updatedBook.category_id).toBe(validCategoryId);

    await request.delete(`/api/books/${bookId}`);
  });
});
