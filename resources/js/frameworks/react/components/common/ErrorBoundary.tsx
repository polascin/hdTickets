/**
 * HD Tickets - React Error Boundary
 * 
 * Error boundary component for graceful error handling
 */

import React, { Component, ErrorInfo, ReactNode } from 'react';

interface Props {
  children: ReactNode;
  componentName?: string;
  fallback?: (error: Error, errorInfo: ErrorInfo) => ReactNode;
  onError?: (error: Error, errorInfo: ErrorInfo) => void;
}

interface State {
  hasError: boolean;
  error: Error | null;
  errorInfo: ErrorInfo | null;
}

class ErrorBoundary extends Component<Props, State> {
  constructor(props: Props) {
    super(props);
    this.state = {
      hasError: false,
      error: null,
      errorInfo: null
    };
  }

  static getDerivedStateFromError(error: Error): Partial<State> {
    return {
      hasError: true,
      error
    };
  }

  componentDidCatch(error: Error, errorInfo: ErrorInfo) {
    console.error('React Error Boundary caught an error:', error, errorInfo);
    
    this.setState({
      error,
      errorInfo
    });

    // Call the onError callback if provided
    if (this.props.onError) {
      this.props.onError(error, errorInfo);
    }

    // Report to error tracking service if available
    if (window.Sentry) {
      window.Sentry.captureException(error, {
        tags: { 
          component: this.props.componentName || 'Unknown',
          errorBoundary: true 
        },
        extra: errorInfo
      });
    }
  }

  render() {
    if (this.state.hasError) {
      // Use custom fallback if provided
      if (this.props.fallback && this.state.error && this.state.errorInfo) {
        return this.props.fallback(this.state.error, this.state.errorInfo);
      }

      // Default error UI
      return (
        <div className="error-boundary p-6 bg-red-50 border border-red-200 rounded-lg">
          <div className="flex items-start">
            <div className="flex-shrink-0">
              <svg
                className="h-6 w-6 text-red-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"
                />
              </svg>
            </div>
            <div className="ml-3 flex-1">
              <h3 className="text-lg font-medium text-red-800">
                {this.props.componentName ? 
                  `${this.props.componentName} Component Error` : 
                  'Component Error'
                }
              </h3>
              <div className="mt-2 text-sm text-red-700">
                <p>Something went wrong while rendering this component.</p>
                {this.state.error && (
                  <details className="mt-2">
                    <summary className="cursor-pointer font-medium">
                      Error Details
                    </summary>
                    <div className="mt-2 p-3 bg-red-100 rounded border text-xs font-mono">
                      <div className="font-semibold">Error:</div>
                      <div className="mb-2">{this.state.error.message}</div>
                      {this.state.error.stack && (
                        <>
                          <div className="font-semibold">Stack Trace:</div>
                          <pre className="whitespace-pre-wrap break-all">
                            {this.state.error.stack}
                          </pre>
                        </>
                      )}
                      {this.state.errorInfo && this.state.errorInfo.componentStack && (
                        <>
                          <div className="font-semibold mt-2">Component Stack:</div>
                          <pre className="whitespace-pre-wrap">
                            {this.state.errorInfo.componentStack}
                          </pre>
                        </>
                      )}
                    </div>
                  </details>
                )}
              </div>
              <div className="mt-4 flex space-x-3">
                <button
                  onClick={() => window.location.reload()}
                  className="bg-red-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                >
                  Refresh Page
                </button>
                <button
                  onClick={() => this.setState({ hasError: false, error: null, errorInfo: null })}
                  className="bg-white text-red-600 px-4 py-2 rounded text-sm font-medium border border-red-300 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                >
                  Try Again
                </button>
              </div>
            </div>
          </div>
        </div>
      );
    }

    return this.props.children;
  }
}

export default ErrorBoundary;