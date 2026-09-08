import styles from '@/components/Pagination.module.css';

interface PaginationProps {
  currentPage: number;
  lastPage: number;
  onPageChange: (page: number) => void;
}

export function Pagination({ currentPage, lastPage, onPageChange }: PaginationProps) {
  if (lastPage <= 1) {
    return null;
  }

  return (
    <nav className={styles.pagination} aria-label="Paginação">
      <button
        type="button"
        disabled={currentPage <= 1}
        onClick={() => onPageChange(currentPage - 1)}
      >
        Anterior
      </button>
      <span>
        Página {currentPage} de {lastPage}
      </span>
      <button
        type="button"
        disabled={currentPage >= lastPage}
        onClick={() => onPageChange(currentPage + 1)}
      >
        Próxima
      </button>
    </nav>
  );
}
