export const {ratings} = {
    ratings: [
        {
            rating: 5,
            description: 'Oh my god, that was amazing! (5/5)'
        },
        {
            rating: 4,
            description: 'Really Good (4/5)',
        },
        {
            rating: 3.5,
            description: 'Good and Really Good (3.5/5)',
        },
        {
            rating: 3,
            description: 'Good (3/5)',
        },
        {
            rating: 2,
            description: 'Good and Bad (2/5)',
        },
        {
            rating: 1,
            description: 'Just Bad (1/5)',
        },
        {
            rating: 0,
            description: 'What did I even read?! (0/5)'
        }
    ]
}

let bookTypesRequest = null
let cachedBookTypes = null

export async function getBookTypes(forceRefresh = false) {
    if (cachedBookTypes && !forceRefresh) {
        return cachedBookTypes
    }

    if (!bookTypesRequest || forceRefresh) {
        bookTypesRequest = axios.get('/json/book-types').then(({ data }) => {
            cachedBookTypes = data
            return data
        }).finally(() => {
            if (forceRefresh) {
                bookTypesRequest = null
            }
        })
    }

    return bookTypesRequest
}
