import { useState, useCallback } from 'react'
import { usePersistentState } from '@/shared/hooks/usePersistentState'

interface SavedSearch {
  id: string
  name: string
  filters: any
  createdAt: Date
  lastUsed?: Date
}

export const useSavedSearches = () => {
  const [savedSearches, setSavedSearches] = usePersistentState<SavedSearch[]>('saved-searches', [])
  const [isLoading, setIsLoading] = useState(false)

  const saveSearch = useCallback(async (searchData: Omit<SavedSearch, 'id'>) => {
    setIsLoading(true)
    
    try {
      const newSearch: SavedSearch = {
        ...searchData,
        id: `search-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
      }
      
      setSavedSearches(prev => [newSearch, ...prev])
      
      // In a real app, you'd save to the backend
      // await api.post('/api/saved-searches', newSearch)
      
      return newSearch
    } finally {
      setIsLoading(false)
    }
  }, [setSavedSearches])

  const deleteSavedSearch = useCallback(async (searchId: string) => {
    setIsLoading(true)
    
    try {
      setSavedSearches(prev => prev.filter(search => search.id !== searchId))
      
      // In a real app, you'd delete from the backend
      // await api.delete(`/api/saved-searches/${searchId}`)
    } finally {
      setIsLoading(false)
    }
  }, [setSavedSearches])

  const updateLastUsed = useCallback(async (searchId: string) => {
    setSavedSearches(prev => 
      prev.map(search => 
        search.id === searchId 
          ? { ...search, lastUsed: new Date() }
          : search
      )
    )
  }, [setSavedSearches])

  return {
    savedSearches,
    saveSearch,
    deleteSavedSearch,
    updateLastUsed,
    isLoading,
  }
}

export default useSavedSearches
