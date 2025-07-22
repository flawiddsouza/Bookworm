/**
 * Document title management utility
 */

const DEFAULT_TITLE = 'Bookworm'

/**
 * Set the document title with optional suffix
 * @param {string} title - The page title
 * @param {boolean} includeAppName - Whether to include the app name
 */
export function setDocumentTitle(title, includeAppName = true) {
    if (!title || title === DEFAULT_TITLE) {
        document.title = DEFAULT_TITLE
        return
    }

    document.title = includeAppName ? `${title} - ${DEFAULT_TITLE}` : title
}

/**
 * Reset document title to default
 */
export function resetDocumentTitle() {
    document.title = DEFAULT_TITLE
}

/**
 * Get page title based on route
 * @param {object} route - Vue router route object
 * @returns {string} - The appropriate title for the route
 */
export function getRouteTitle(route) {
    const routeTitles = {
        '/': 'Home',
        '/manage/books': 'Manage Books',
        '/manage/authors': 'Manage Authors',
        '/manage/series': 'Manage Series',
        '/manage/book-types': 'Manage Book Types',
        '/import': 'Import'
    }

    return routeTitles[route.path] || DEFAULT_TITLE
}
