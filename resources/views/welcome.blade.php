<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Library API Gateway</title>
    <style>
        :root {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #172033;
            background: #f5f7fb;
            font-synthesis: none;
            text-rendering: optimizeLegibility;
            --line: #dce2ec;
            --muted: #64748b;
            --brand: #4056c8;
        }

        * { box-sizing: border-box; }
        body { margin: 0; min-width: 320px; }
        button, input { font: inherit; }
        button { cursor: pointer; }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 18px max(24px, calc((100vw - 1200px) / 2));
            color: white;
            background: #172033;
        }

        .brand { display: flex; align-items: center; gap: 12px; }
        .brand-mark {
            display: grid;
            width: 40px;
            height: 40px;
            place-items: center;
            border-radius: 12px;
            background: #465bd8;
            font-size: 19px;
            font-weight: 800;
        }
        .brand strong, .brand small { display: block; }
        .brand small { margin-top: 3px; color: #aab4ca; }
        .version {
            padding: 5px 9px;
            border: 1px solid #46536d;
            border-radius: 20px;
            color: #dce4f6;
            font-size: 12px;
        }

        main { width: min(1200px, calc(100% - 40px)); margin: 36px auto 64px; }
        .intro { margin-bottom: 26px; }
        .eyebrow {
            margin: 0 0 8px;
            color: var(--brand);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }
        h1 { margin: 0 0 10px; font-size: clamp(28px, 4vw, 38px); }
        .intro-copy { max-width: 720px; margin: 0; color: var(--muted); line-height: 1.6; }

        .toolbar {
            display: grid;
            grid-template-columns: minmax(240px, 1fr) minmax(280px, 1.1fr);
            gap: 16px;
            margin: 24px 0;
        }
        .toolbar-card, .endpoint-group {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: white;
            box-shadow: 0 3px 12px rgb(15 23 42 / 3%);
        }
        .toolbar-card { padding: 16px; }
        .toolbar label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 700; }
        input {
            width: 100%;
            min-height: 42px;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: white;
        }
        input:focus { border-color: var(--brand); outline: 2px solid #e0e7ff; }
        .helper { margin: 8px 0 0; color: var(--muted); font-size: 12px; }
        .base-url { color: #334155; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }

        .group-list { display: grid; gap: 18px; }
        .endpoint-group { overflow: hidden; }
        .group-heading {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 18px;
            border-bottom: 1px solid var(--line);
        }
        .group-heading h2 { margin: 0; font-size: 17px; }
        .group-heading span { color: var(--muted); font-size: 12px; }
        .endpoint-list { display: grid; gap: 1px; background: #edf0f5; }
        .endpoint { background: white; }
        .endpoint summary {
            display: grid;
            grid-template-columns: 82px minmax(190px, 1.1fr) minmax(180px, 1fr) auto;
            align-items: center;
            gap: 13px;
            min-height: 64px;
            padding: 12px 18px;
            cursor: pointer;
            list-style: none;
        }
        .endpoint summary::-webkit-details-marker { display: none; }
        .endpoint summary:hover { background: #f8faff; }
        .method {
            display: inline-flex;
            min-width: 68px;
            justify-content: center;
            padding: 6px 8px;
            border-radius: 5px;
            color: white;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 11px;
            font-weight: 800;
        }
        .method-get { background: #16835d; }
        .method-post { background: #c17a12; }
        .method-put, .method-patch { background: #4056c8; }
        .method-delete { background: #c2414b; }
        .path { overflow-wrap: anywhere; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 13px; font-weight: 700; }
        .summary-description { color: #475569; font-size: 13px; }
        .access {
            padding: 4px 8px;
            border: 1px solid #dce2ec;
            border-radius: 20px;
            color: #475569;
            font-size: 11px;
            white-space: nowrap;
        }

        .endpoint-detail {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(240px, .8fr);
            gap: 20px;
            padding: 18px;
            border-top: 1px solid var(--line);
            background: #fbfcfe;
        }
        .endpoint-detail h3 { margin: 0 0 8px; font-size: 13px; }
        .endpoint-detail p { margin: 0 0 12px; color: #526174; font-size: 13px; line-height: 1.5; }
        pre {
            overflow: auto;
            margin: 0;
            padding: 12px;
            border: 1px solid #e1e6ef;
            border-radius: 7px;
            color: #e2e8f0;
            background: #172033;
            font-size: 12px;
            line-height: 1.55;
        }
        .empty { display: none; padding: 30px; color: var(--muted); text-align: center; }
        .empty.visible { display: block; }
        footer { margin-top: 24px; color: var(--muted); font-size: 12px; text-align: center; }

        @media (max-width: 760px) {
            .topbar { padding: 14px 18px; }
            main { width: min(100% - 24px, 1200px); margin-top: 24px; }
            .toolbar { grid-template-columns: 1fr; }
            .endpoint summary {
                grid-template-columns: 72px minmax(0, 1fr) auto;
                gap: 9px;
                padding: 12px;
            }
            .summary-description { grid-column: 2 / 3; }
            .access { grid-column: 3; grid-row: 1 / span 2; }
            .endpoint-detail { grid-template-columns: 1fr; }
        }
        @media (max-width: 450px) {
            .version { display: none; }
            .endpoint summary { grid-template-columns: 1fr auto; }
            .method { min-width: 62px; justify-self: start; }
            .path { grid-column: 1; grid-row: 2; }
            .summary-description { grid-column: 1 / -1; grid-row: 3; }
            .access { grid-column: 2; grid-row: 1 / span 2; }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="brand">
            <span class="brand-mark">L</span>
            <div>
                <strong>Library API Gateway</strong>
                <small>API reference</small>
            </div>
        </div>
        <span class="version">REST API · JSON</span>
    </header>

    <main>
        <section class="intro">
            <p class="eyebrow">Developer documentation</p>
            <h1>Library API</h1>
            <p class="intro-copy">
                Browse the API Gateway endpoints, access requirements, and example request payloads.
                Protected endpoints require a Sanctum bearer token obtained from the login endpoint.
            </p>
        </section>

        <section class="toolbar" aria-label="API reference tools">
            <div class="toolbar-card">
                <label for="endpoint-search">Filter endpoints</label>
                <input id="endpoint-search" type="search" placeholder="Search by path, method, or description...">
                <p class="helper">Filtering is applied across all endpoint groups.</p>
            </div>
            <div class="toolbar-card">
                <label>API base URL</label>
                <div class="base-url">{{ url('/api') }}</div>
                <p class="helper">All listed paths are relative to this URL.</p>
            </div>
        </section>

        <div id="endpoint-groups" class="group-list"></div>
        <div id="empty-state" class="empty">No endpoints match your search.</div>

        <footer>
            Authentication: <code>Authorization: Bearer &lt;token&gt;</code>
            <span aria-hidden="true"> · </span>
            Content type: <code>application/json</code>
        </footer>
    </main>

    <script>
        const groups = [
            {
                name: 'Authentication',
                note: 'Sign in and manage the current session',
                endpoints: [
                    {
                        method: 'POST', path: '/login', access: 'Public',
                        description: 'Authenticate and receive a bearer token.',
                        body: { email: 'librarian@library.test', password: 'your-password' },
                    },
                    {
                        method: 'GET', path: '/me', access: 'Authenticated',
                        description: 'Get the authenticated user profile.',
                    },
                    {
                        method: 'POST', path: '/logout', access: 'Authenticated',
                        description: 'Revoke the current access token.',
                    },
                ],
            },
            {
                name: 'Books',
                note: 'Browse the public book catalogue',
                endpoints: [
                    {
                        method: 'GET', path: '/books', access: 'Public',
                        description: 'List books. Supports the search query parameter.',
                        query: '?search=clean',
                    },
                    {
                        method: 'GET', path: '/books/{id}', access: 'Public',
                        description: 'Get a book and its copies by book ID.',
                    },
                    {
                        method: 'GET', path: '/books/{id}/copies', access: 'Public',
                        description: 'List copies belonging to a book.',
                    },
                    {
                        method: 'POST', path: '/books', access: 'Librarian',
                        description: 'Create a book.',
                        body: {
                            author_id: 1, title: 'Example title', isbn: '9780000000000',
                            description: 'Book description', replacement_price: 250000,
                            published_year: 2024,
                        },
                    },
                    {
                        method: 'PUT', path: '/books/{id}', access: 'Librarian',
                        description: 'Update book details.',
                        body: { title: 'Updated title' },
                    },
                    {
                        method: 'POST', path: '/books/{id}/copies', access: 'Librarian',
                        description: 'Add a copy to a book.',
                        body: { copy_code: 'COPY-001' },
                    },
                    {
                        method: 'PUT', path: '/book-copies/{id}', access: 'Librarian',
                        description: 'Update a copy code.',
                        body: { copy_code: 'COPY-002' },
                    },
                    {
                        method: 'DELETE', path: '/book-copies/{id}', access: 'Librarian',
                        description: 'Delete a copy when its status is AVAILABLE or LOST.',
                    },
                ],
            },
            {
                name: 'Authors',
                note: 'Browse author records',
                endpoints: [
                    {
                        method: 'GET', path: '/authors', access: 'Public',
                        description: 'List authors.',
                    },
                    {
                        method: 'GET', path: '/authors/{id}', access: 'Public',
                        description: 'Get an author by ID.',
                    },
                    {
                        method: 'POST', path: '/authors', access: 'Librarian',
                        description: 'Create an author.',
                        body: { name: 'Author name', biography: 'Author biography' },
                    },
                    {
                        method: 'PUT', path: '/authors/{id}', access: 'Librarian',
                        description: 'Update an author.',
                        body: { name: 'Updated author name', biography: 'Updated biography' },
                    },
                    {
                        method: 'DELETE', path: '/authors/{id}', access: 'Librarian',
                        description: 'Delete an author who has no books.',
                    },
                ],
            },
            {
                name: 'Members',
                note: 'Member records and profiles',
                endpoints: [
                    {
                        method: 'GET', path: '/members', access: 'Librarian',
                        description: 'List library members.',
                    },
                    {
                        method: 'POST', path: '/members', access: 'Librarian',
                        description: 'Create a member and linked login account.',
                        body: { member_number: 'MBR-001', name: 'Member name', email: 'member@example.test' },
                    },
                    {
                        method: 'PATCH', path: '/members/{id}', access: 'Librarian',
                        description: 'Update a member name and email.',
                        body: { name: 'Updated name', email: 'updated@example.test' },
                    },
                ],
            },
            {
                name: 'Lendings',
                note: 'Borrowing, returns, and loss reports',
                endpoints: [
                    {
                        method: 'GET', path: '/lendings', access: 'Authenticated',
                        description: 'List lendings. Members only receive their own records.',
                    },
                    {
                        method: 'POST', path: '/lendings', access: 'Librarian',
                        description: 'Create a lending for an active member and book copy.',
                        body: { member_id: 1, book_copy_id: 1, due_at: '2026-12-01' },
                    },
                    {
                        method: 'POST', path: '/lendings/{id}/return', access: 'Librarian',
                        description: 'Return a borrowed book and release the copy.',
                    },
                    {
                        method: 'POST', path: '/lendings/{id}/report-lost', access: 'Librarian',
                        description: 'Report a lost book and update the copy status.',
                    },
                ],
            },
            {
                name: 'Administration',
                note: 'Manage user accounts (ADMIN only)',
                endpoints: [
                    {
                        method: 'GET', path: '/users', access: 'Admin',
                        description: 'List registered user accounts.',
                    },
                    {
                        method: 'POST', path: '/users', access: 'Admin',
                        description: 'Create an ADMIN or LIBRARIAN account.',
                        body: {
                            name: 'User name', email: 'user@example.test',
                            password: 'minimum-eight-characters', role: 'LIBRARIAN',
                        },
                    },
                    {
                        method: 'POST', path: '/users/{id}/reset-password', access: 'Admin',
                        description: 'Reset a user password. Password confirmation is required.',
                        body: {
                            password: 'new-password-123', password_confirmation: 'new-password-123',
                        },
                    },
                    {
                        method: 'PUT', path: '/users/{id}/status', access: 'Admin',
                        description: 'Activate or deactivate a user account.',
                        body: { is_active: false },
                    },
                ],
            },
        ];

        const groupsRoot = document.getElementById('endpoint-groups');
        const searchInput = document.getElementById('endpoint-search');
        const emptyState = document.getElementById('empty-state');

        function createEndpoint(endpoint) {
            const details = document.createElement('details');
            details.className = 'endpoint';

            const summary = document.createElement('summary');
            const method = document.createElement('span');
            method.className = `method method-${endpoint.method.toLowerCase()}`;
            method.textContent = endpoint.method;

            const path = document.createElement('code');
            path.className = 'path';
            path.textContent = endpoint.path;

            const description = document.createElement('span');
            description.className = 'summary-description';
            description.textContent = endpoint.description;

            const access = document.createElement('span');
            access.className = 'access';
            access.textContent = endpoint.access;
            summary.append(method, path, description, access);

            const detail = document.createElement('div');
            detail.className = 'endpoint-detail';
            const info = document.createElement('div');
            const infoHeading = document.createElement('h3');
            infoHeading.textContent = 'Endpoint';
            const infoText = document.createElement('p');
            infoText.textContent = `${endpoint.method} {{ url('/api') }}${endpoint.path}${endpoint.query ?? ''}`;
            info.append(infoHeading, infoText);

            const payload = document.createElement('div');
            const payloadHeading = document.createElement('h3');
            payloadHeading.textContent = endpoint.body ? 'Example request body' : 'Request';
            const pre = document.createElement('pre');
            pre.textContent = endpoint.body
                ? JSON.stringify(endpoint.body, null, 2)
                : endpoint.query
                    ? `GET ${endpoint.path}${endpoint.query}`
                    : `${endpoint.method} ${endpoint.path}`;
            payload.append(payloadHeading, pre);
            detail.append(info, payload);
            details.append(summary, detail);

            details.dataset.search = [
                endpoint.method, endpoint.path, endpoint.description, endpoint.access,
            ].join(' ').toLowerCase();
            return details;
        }

        for (const group of groups) {
            const section = document.createElement('section');
            section.className = 'endpoint-group';

            const heading = document.createElement('div');
            heading.className = 'group-heading';
            const title = document.createElement('h2');
            title.textContent = group.name;
            const note = document.createElement('span');
            note.textContent = group.note;
            heading.append(title, note);

            const list = document.createElement('div');
            list.className = 'endpoint-list';
            for (const endpoint of group.endpoints) {
                list.append(createEndpoint(endpoint));
            }

            section.append(heading, list);
            groupsRoot.append(section);
        }

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLowerCase();
            let visibleCount = 0;

            for (const section of groupsRoot.children) {
                let visibleInGroup = 0;
                for (const endpoint of section.querySelectorAll('.endpoint')) {
                    const visible = endpoint.dataset.search.includes(query);
                    endpoint.hidden = !visible;
                    if (visible) visibleInGroup += 1;
                }
                section.hidden = visibleInGroup === 0;
                visibleCount += visibleInGroup;
            }

            emptyState.classList.toggle('visible', visibleCount === 0);
        });
    </script>
</body>
</html>
