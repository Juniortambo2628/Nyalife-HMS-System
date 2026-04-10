import React from 'react';
import {
  DndContext, 
  closestCenter,
  KeyboardSensor,
  PointerSensor,
  useSensor,
  useSensors,
} from '@dnd-kit/core';
import {
  arrayMove,
  SortableContext,
  sortableKeyboardCoordinates,
  verticalListSortingStrategy,
  useSortable
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

function SortableItem({ id, label }) {
  const {
    attributes,
    listeners,
    setNodeRef,
    transform,
    transition,
  } = useSortable({ id });
  
  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
  };
  
  return (
    <div 
      ref={setNodeRef} 
      style={style} 
      {...attributes} 
      {...listeners}
      className="bg-white border rounded-pill px-4 py-3 mb-2 d-flex align-items-center cursor-grab hover-shadow transition-all"
    >
      <i className="fas fa-grip-vertical text-gray-300 me-3"></i>
      <span className="fw-bold text-gray-700 text-capitalize">{label}</span>
      <div className="ms-auto badge bg-light text-muted rounded-pill">Drag to reorder</div>
    </div>
  );
}

export default function SectionOrder({ value, onChange }) {
  const sections = value.split(',').filter(Boolean);
  
  const sensors = useSensors(
    useSensor(PointerSensor),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    })
  );

  function handleDragEnd(event) {
    const { active, over } = event;
    
    if (active.id !== over.id) {
      const oldIndex = sections.indexOf(active.id);
      const newIndex = sections.indexOf(over.id);
      const newOrder = arrayMove(sections, oldIndex, newIndex);
      onChange(newOrder.join(','));
    }
  }

  return (
    <div className="section-order-tool card border-0 shadow-sm rounded-2xl p-4 bg-light">
      <h6 className="fw-bold mb-4 text-gray-500 d-flex align-items-center">
        <i className="fas fa-sort-numeric-down me-2"></i> Landing Page Layout
      </h6>
      <DndContext 
        sensors={sensors}
        collisionDetection={closestCenter}
        onDragEnd={handleDragEnd}
      >
        <SortableContext 
          items={sections}
          strategy={verticalListSortingStrategy}
        >
          {sections.map(id => (
            <SortableItem key={id} id={id} label={id} />
          ))}
        </SortableContext>
      </DndContext>
      <p className="extra-small text-muted mt-3 mb-0">
        <i className="fas fa-info-circle me-1"></i> These sections appear on the landing page in the order shown above.
      </p>
      
      <style>{`
        .cursor-grab { cursor: grab; }
        .cursor-grab:active { cursor: grabbing; }
        .hover-shadow:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
      `}</style>
    </div>
  );
}
